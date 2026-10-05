package main

import (
	"bytes"
	"fmt"
	"strings"
)

const copyFitTolerance = 0.02

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
		j.log("Skipping copies: the page does not fit more than once on a %s sheet\n", j.cfg.sheetSize)
		return comp, nil
	}

	cols = min(cols, copies)
	rows = (copies + cols - 1) / cols

	var ticket bytes.Buffer
	if err := comp.Write(&ticket); err != nil {
		return nil, err
	}

	offsetX := (sheetWidth - float64(cols)*g.paperWidth) / 2
	offsetY := (sheetHeight - float64(rows)*g.paperHeight) / 2

	sheetBytes, err := j.renderer.RenderHTMLToPDFBytes(buildSheetHTML(totalPages, sheetWidth, sheetHeight, offsetX, offsetY, g.paperWidth, g.paperHeight, cols, rows, copies), RenderOptions{
		PaperWidthInches:  sheetWidth,
		PaperHeightInches: sheetHeight,
		Scale:             1.0,
		Timeout:           j.timeout(),
	})
	if err != nil {
		return nil, fmt.Errorf("failed to render copy sheet: %w", err)
	}

	sheet, err := NewComposerFromBytes(sheetBytes)
	if err != nil {
		return nil, err
	}
	if sheet.PageCount() != totalPages {
		return nil, fmt.Errorf("copy sheet produced %d pages for a %d page document", sheet.PageCount(), totalPages)
	}

	for i := 0; i < copies; i++ {
		row, col := i/cols, i%cols
		placement := StampPlacement{
			Pos:     "bl",
			OffsetX: InchesToPoints(offsetX + float64(col)*g.paperWidth),
			OffsetY: InchesToPoints(sheetHeight - offsetY - float64(row+1)*g.paperHeight),
		}
		if err := sheet.StampPagesBytes(ticket.Bytes(), placement, true, nil, false); err != nil {
			return nil, err
		}
	}

	j.log("Placed %d copies on each %s sheet\n", copies, j.cfg.sheetSize)

	return sheet, nil
}

func buildSheetHTML(totalPages int, sheetWidth, sheetHeight, offsetX, offsetY, itemWidth, itemHeight float64, cols, rows, copies int) string {
	var guides strings.Builder

	for c := 1; c < cols; c++ {
		height := float64(rows) * itemHeight
		if c >= copies-(rows-1)*cols {
			height = float64(rows-1) * itemHeight
		}
		fmt.Fprintf(&guides, `<i style="left:%.4fin;top:%.4fin;height:%.4fin;border-left:1px dashed #9aa4b2"></i>`, offsetX+float64(c)*itemWidth, offsetY, height)
	}

	for r := 1; r < rows; r++ {
		width := float64(cols) * itemWidth
		fmt.Fprintf(&guides, `<i style="left:%.4fin;top:%.4fin;width:%.4fin;border-top:1px dashed #9aa4b2"></i>`, offsetX, offsetY+float64(r)*itemHeight, width)
	}

	var sb strings.Builder
	fmt.Fprintf(&sb, `<!DOCTYPE html><html><head><meta charset="utf-8"><style>
@page{margin:0;size:%.4fin %.4fin}
html,body{margin:0;padding:0}
.sheet{position:relative;width:%.4fin;height:%.4fin;overflow:hidden;break-after:page;page-break-after:always}
.sheet:last-child{break-after:auto;page-break-after:auto}
.sheet i{position:absolute;display:block}
</style></head><body>`, sheetWidth, sheetHeight, sheetWidth, sheetHeight-0.02)

	for p := 0; p < totalPages; p++ {
		sb.WriteString(`<div class="sheet">`)
		sb.WriteString(guides.String())
		sb.WriteString(`</div>`)
	}

	sb.WriteString(`</body></html>`)
	return sb.String()
}
