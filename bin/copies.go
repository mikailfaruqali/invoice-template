package main

import (
	"bytes"
	"fmt"
	"strings"
)

const copyFitTolerance = 0.02

const copyStackGap = 6 / 25.4

func copiesGrid(itemWidth, itemHeight, sheetWidth, sheetHeight float64) (int, int) {
	cols := int((sheetWidth + copyFitTolerance) / itemWidth)
	rows := int((sheetHeight + copyFitTolerance) / itemHeight)
	return cols, rows
}

func (j *job) impose(comp *Composer, totalPages int) (*Composer, error) {
	g := j.geo

	sheetWidth, sheetHeight, err := GetPaperDimensions(j.cfg.sheetSize, "portrait")
	if err != nil {
		return nil, fmt.Errorf("invalid --sheet-size: %w", err)
	}

	cols, rows := copiesGrid(g.paperWidth, g.paperHeight, sheetWidth, sheetHeight)
	if landCols, landRows := copiesGrid(g.paperWidth, g.paperHeight, sheetHeight, sheetWidth); landCols*landRows > cols*rows {
		cols, rows = landCols, landRows
		sheetWidth, sheetHeight = sheetHeight, sheetWidth
	}

	copies := min(j.cfg.copies, cols*rows)
	if copies <= 1 {
		return j.stackCopies(comp, totalPages)
	}

	cols = min(cols, copies)
	rows = (copies + cols - 1) / cols

	return j.placeCopies(comp, totalPages, copySheet{
		width:   sheetWidth,
		height:  sheetHeight,
		offsetX: (sheetWidth - float64(cols)*g.paperWidth) / 2,
		offsetY: (sheetHeight - float64(rows)*g.paperHeight) / 2,
		itemW:   g.paperWidth,
		itemH:   g.paperHeight,
		cols:    cols,
		rows:    rows,
		copies:  copies,
	})
}

func (j *job) stackCopies(comp *Composer, totalPages int) (*Composer, error) {
	g := j.geo

	if totalPages != 1 || j.contentHeight <= 0 {
		j.log("Skipping copies: only single-page documents can share a sheet\n")
		return comp, nil
	}

	short := *g
	bottom := j.contentBottom
	if !j.bottomBands {
		short.marginBottom, bottom = 0, 0
	}

	used := j.contentTop + j.contentHeight + bottom + copyFitTolerance
	copies := min(j.cfg.copies, int((g.paperHeight+copyStackGap+copyFitTolerance)/(used+copyStackGap)))
	if copies <= 1 {
		j.log("Skipping copies: the content is too tall to fit more than once on the page\n")
		return comp, nil
	}

	original := j.geo
	short.paperHeight = used
	j.geo = &short

	ticket, pages, err := j.build(j.html[0], j.html[1], j.html[2], j.html[3], j.html[4])
	j.geo = original

	if err != nil {
		return nil, err
	}
	if pages != 1 {
		j.log("Skipping copies: the content does not fit on a shortened page\n")
		return comp, nil
	}

	return j.placeCopies(ticket, 1, copySheet{
		width:  g.paperWidth,
		height: g.paperHeight,
		itemW:  g.paperWidth,
		itemH:  used,
		gapY:   copyStackGap,
		cols:   1,
		rows:   copies,
		copies: copies,
	})
}

type copySheet struct {
	width, height    float64
	offsetX, offsetY float64
	itemW, itemH     float64
	gapY             float64
	cols, rows       int
	copies           int
}

func (j *job) placeCopies(comp *Composer, totalPages int, sheet copySheet) (*Composer, error) {
	var ticket bytes.Buffer
	if err := comp.Write(&ticket); err != nil {
		return nil, err
	}

	sheetBytes, err := j.renderer.RenderHTMLToPDFBytes(buildSheetHTML(totalPages, sheet), RenderOptions{
		PaperWidthInches:  sheet.width,
		PaperHeightInches: sheet.height,
		Scale:             1.0,
		Timeout:           j.timeout(),
	})
	if err != nil {
		return nil, fmt.Errorf("failed to render copy sheet: %w", err)
	}

	composed, err := NewComposerFromBytes(sheetBytes)
	if err != nil {
		return nil, err
	}
	if composed.PageCount() != totalPages {
		return nil, fmt.Errorf("copy sheet produced %d pages for a %d page document", composed.PageCount(), totalPages)
	}

	for i := 0; i < sheet.copies; i++ {
		row, col := i/sheet.cols, i%sheet.cols
		placement := StampPlacement{
			Pos:     "bl",
			OffsetX: InchesToPoints(sheet.offsetX + float64(col)*sheet.itemW),
			OffsetY: InchesToPoints(sheet.height - sheet.offsetY - float64(row+1)*sheet.itemH - float64(row)*sheet.gapY),
		}
		if err := composed.StampPagesBytes(ticket.Bytes(), placement, true, nil, false); err != nil {
			return nil, err
		}
	}

	j.log("Placed %d copies on each sheet\n", sheet.copies)

	return composed, nil
}

func buildSheetHTML(totalPages int, sheet copySheet) string {
	var guides strings.Builder

	lastRow := sheet.copies - (sheet.rows-1)*sheet.cols

	for c := 1; c < sheet.cols; c++ {
		height := float64(sheet.rows)*sheet.itemH + float64(sheet.rows-1)*sheet.gapY
		if c >= lastRow {
			height = float64(sheet.rows-1)*sheet.itemH + float64(sheet.rows-2)*sheet.gapY
		}
		fmt.Fprintf(&guides, `<i style="left:%.4fin;top:%.4fin;height:%.4fin;border-left:1px dashed #9aa4b2"></i>`, sheet.offsetX+float64(c)*sheet.itemW, sheet.offsetY, height)
	}

	for r := 1; r < sheet.rows; r++ {
		fmt.Fprintf(&guides, `<i style="left:%.4fin;top:%.4fin;width:%.4fin;border-top:1px dashed #9aa4b2"></i>`, sheet.offsetX, sheet.offsetY+float64(r)*(sheet.itemH+sheet.gapY)-sheet.gapY/2, float64(sheet.cols)*sheet.itemW)
	}

	var sb strings.Builder
	fmt.Fprintf(&sb, `<!DOCTYPE html><html><head><meta charset="utf-8"><style>
@page{margin:0;size:%.4fin %.4fin}
html,body{margin:0;padding:0}
.sheet{position:relative;width:%.4fin;height:%.4fin;overflow:hidden;break-after:page;page-break-after:always}
.sheet:last-child{break-after:auto;page-break-after:auto}
.sheet i{position:absolute;display:block}
</style></head><body>`, sheet.width, sheet.height, sheet.width, sheet.height-0.02)

	for p := 0; p < totalPages; p++ {
		sb.WriteString(`<div class="sheet">`)
		sb.WriteString(guides.String())
		sb.WriteString(`</div>`)
	}

	sb.WriteString(`</body></html>`)
	return sb.String()
}
