package main

import (
	"bytes"
	"fmt"
	"io"

	"github.com/pdfcpu/pdfcpu/pkg/api"
	"github.com/pdfcpu/pdfcpu/pkg/pdfcpu"
	"github.com/pdfcpu/pdfcpu/pkg/pdfcpu/model"
	"github.com/pdfcpu/pdfcpu/pkg/pdfcpu/types"
)

type Composer struct {
	ctx *model.Context
}

func defaultConf() *model.Configuration {
	conf := model.NewDefaultConfiguration()
	conf.ValidationMode = model.ValidationRelaxed
	conf.Cmd = model.ADDWATERMARKS
	conf.Optimize = false
	return conf
}

func NewComposerFromBytes(data []byte) (*Composer, error) {
	ctx, err := api.ReadContext(bytes.NewReader(data), defaultConf())
	if err != nil {
		return nil, fmt.Errorf("failed to parse PDF: %w", err)
	}
	if err := ctx.EnsurePageCount(); err != nil {
		return nil, fmt.Errorf("failed to count PDF pages: %w", err)
	}
	return &Composer{ctx: ctx}, nil
}

func (c *Composer) PageCount() int { return c.ctx.PageCount }

func (c *Composer) StampBandBytes(bandBytes []byte, placement StampPlacement, multi bool, pages types.IntSet) error {
	desc := fmt.Sprintf("pos:%s, scale:1.0 abs, rot:0, off:%.2f %.2f",
		placement.Pos, placement.OffsetX, placement.OffsetY)

	wm, err := api.PDFWatermark("band.pdf", desc, true, false, types.POINTS)
	if err != nil {
		return fmt.Errorf("failed to create stamp: %w", err)
	}
	wm.PDF = bytes.NewReader(bandBytes)

	if multi {
		wm.PdfPageNrSrc = 0
		wm.PdfMultiStartPageNrSrc = 1
		wm.PdfMultiStartPageNrDest = 1
	} else {
		wm.PdfPageNrSrc = 1
	}

	if err := pdfcpu.AddWatermarks(c.ctx, pages, wm); err != nil {
		return fmt.Errorf("failed to apply stamp: %w", err)
	}
	return nil
}

func (c *Composer) WatermarkBytes(watermarkBytes []byte) error {
	wm, err := api.PDFWatermark("watermark.pdf", "pos:c, scale:1.0 abs, rot:0", true, false, types.POINTS)
	if err != nil {
		return fmt.Errorf("failed to create watermark: %w", err)
	}
	wm.PDF = bytes.NewReader(watermarkBytes)
	wm.PdfPageNrSrc = 1

	if err := pdfcpu.AddWatermarks(c.ctx, nil, wm); err != nil {
		return fmt.Errorf("failed to apply watermark: %w", err)
	}
	return nil
}

func (c *Composer) Write(w io.Writer) error {
	if err := api.WriteContext(c.ctx, w); err != nil {
		return fmt.Errorf("failed to write PDF: %w", err)
	}
	return nil
}

func GetPDFPageCountFromBytes(data []byte) (int, error) {
	ctx, err := api.ReadContext(bytes.NewReader(data), defaultConf())
	if err != nil {
		return 0, fmt.Errorf("failed to parse PDF: %w", err)
	}
	if err := ctx.EnsurePageCount(); err != nil {
		return 0, fmt.Errorf("failed to count pages: %w", err)
	}
	return ctx.PageCount, nil
}

type StampPlacement struct {
	Pos     string
	OffsetX float64
	OffsetY float64
}
