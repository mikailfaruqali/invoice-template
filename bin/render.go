package main

import (
	"fmt"
	"regexp"
	"strconv"
	"strings"
	"sync"
	"time"
)

type geometry struct {
	paperWidth, paperHeight      float64
	marginTop, marginBottom      float64
	marginLeft, marginRight      float64
	headerSpacing, footerSpacing float64
}

func (g *geometry) headerHeight() float64 { return g.marginTop - g.headerSpacing }

func (g *geometry) footerHeight() float64 { return g.marginBottom - g.footerSpacing }

func resolveGeometry(cfg *config) (*geometry, error) {
	var (
		g   geometry
		err error
	)

	if cfg.pageWidth != "" || cfg.pageHeight != "" {
		if cfg.pageWidth == "" || cfg.pageHeight == "" {
			return nil, fmt.Errorf("both --page-width and --page-height must be provided together")
		}
		if g.paperWidth, err = ParseDimensionToInches(cfg.pageWidth); err != nil {
			return nil, fmt.Errorf("invalid --page-width: %w", err)
		}
		if g.paperHeight, err = ParseDimensionToInches(cfg.pageHeight); err != nil {
			return nil, fmt.Errorf("invalid --page-height: %w", err)
		}
		if g.paperWidth <= 0 || g.paperHeight <= 0 {
			return nil, fmt.Errorf("page width and height must be greater than zero")
		}
		if isLandscape(cfg.orientation) {
			g.paperWidth, g.paperHeight = g.paperHeight, g.paperWidth
		}
	} else if g.paperWidth, g.paperHeight, err = GetPaperDimensions(cfg.pageSize, cfg.orientation); err != nil {
		return nil, err
	}

	for _, f := range []struct {
		name string
		src  string
		dst  *float64
	}{
		{"margin-top", cfg.marginTop, &g.marginTop},
		{"margin-bottom", cfg.marginBottom, &g.marginBottom},
		{"margin-left", cfg.marginLeft, &g.marginLeft},
		{"margin-right", cfg.marginRight, &g.marginRight},
		{"header-spacing", cfg.headerSpacing, &g.headerSpacing},
		{"footer-spacing", cfg.footerSpacing, &g.footerSpacing},
	} {
		v, err := ParseDimensionToInches(f.src)
		if err != nil {
			return nil, fmt.Errorf("invalid --%s: %w", f.name, err)
		}
		if v < 0 {
			return nil, fmt.Errorf("invalid --%s: must not be negative", f.name)
		}
		*f.dst = v
	}

	if g.paperHeight-g.marginTop-g.marginBottom <= 0.2 {
		return nil, fmt.Errorf("top and bottom margins leave no room for content on a %.2fin tall page", g.paperHeight)
	}
	if g.paperWidth-g.marginLeft-g.marginRight <= 0.2 {
		return nil, fmt.Errorf("left and right margins leave no room for content on a %.2fin wide page", g.paperWidth)
	}

	return &g, nil
}

type job struct {
	cfg      *config
	geo      *geometry
	renderer *ChromeRenderer
	logf     func(string, ...interface{})
	mu       sync.Mutex
}

func (j *job) log(format string, args ...interface{}) {
	j.mu.Lock()
	defer j.mu.Unlock()
	j.logf(format, args...)
}

func (j *job) timeout() time.Duration {
	return time.Duration(j.cfg.timeoutSeconds) * time.Second
}

func (j *job) build(contentHTML, headerHTML, footerHTML, watermarkHTML string) (*Composer, int, error) {
	g := j.geo

	if headerHTML != "" && g.headerHeight() <= 0 {
		j.log("Skipping header: margin-top leaves no room for it\n")
		headerHTML = ""
	}
	if footerHTML != "" && g.footerHeight() <= 0 {
		j.log("Skipping footer: margin-bottom leaves no room for it\n")
		footerHTML = ""
	}

	if err := j.renderer.Start(); err != nil {
		return nil, 0, err
	}

	var (
		headerBand, footerBand *band
		watermarkBytes         []byte
		errs                   []error
		mu                     sync.Mutex
		bandsWg                sync.WaitGroup
	)

	fail := func(err error) {
		mu.Lock()
		errs = append(errs, err)
		mu.Unlock()
	}

	if watermarkHTML != "" {
		bandsWg.Add(1)
		go func() {
			defer bandsWg.Done()
			data, err := j.renderer.RenderHTMLToPDFBytes(buildWatermarkHTML(watermarkHTML, j.cfg.watermarkOpacity), RenderOptions{
				PaperWidthInches:  g.paperWidth,
				PaperHeightInches: g.paperHeight,
				Scale:             1.0,
				Timeout:           j.timeout(),
			})
			if err != nil {
				fail(fmt.Errorf("failed to render watermark: %w", err))
				return
			}
			mu.Lock()
			watermarkBytes = data
			mu.Unlock()
		}()
	}

	pageCountCh := make(chan int, 1)
	bandTotals := make(chan int, 2)

	startBand := func(html string, spec bandSpec) {
		if html == "" {
			return
		}
		bandsWg.Add(1)
		go func() {
			defer bandsWg.Done()
			total, ok := <-bandTotals
			if !ok {
				return
			}
			b, err := j.renderBand(html, total, spec)
			if err != nil {
				fail(err)
				return
			}
			mu.Lock()
			if spec.name == "header" {
				headerBand = b
			} else {
				footerBand = b
			}
			mu.Unlock()
		}()
	}

	startBand(headerHTML, bandSpec{
		name:      "header",
		height:    g.headerHeight(),
		placement: StampPlacement{Pos: "tc"},
	})
	startBand(footerHTML, bandSpec{
		name:      "footer",
		height:    g.footerHeight(),
		placement: StampPlacement{Pos: "bc"},
	})

	go func() {
		if n, ok := <-pageCountCh; ok {
			bandTotals <- n
			bandTotals <- n
		}
		close(bandTotals)
	}()

	j.log("Rendering content... ")
	contentBytes, err := j.renderer.RenderHTMLToPDFBytesCounted(contentHTML, RenderOptions{
		PaperWidthInches:   g.paperWidth,
		PaperHeightInches:  g.paperHeight,
		MarginTopInches:    g.marginTop,
		MarginBottomInches: g.marginBottom,
		MarginLeftInches:   g.marginLeft,
		MarginRightInches:  g.marginRight,
		Scale:              j.cfg.zoom,
		SmartShrink:        !j.cfg.disableSmartShrinking,
		Timeout:            j.timeout(),
	}, pageCountCh)
	if err != nil {
		j.log("failed\n")
		bandsWg.Wait()
		return nil, 0, fmt.Errorf("failed to render content: %w", err)
	}
	j.log("done\n")

	comp, err := NewComposerFromBytes(contentBytes)
	if err != nil {
		bandsWg.Wait()
		return nil, 0, err
	}
	totalPages := comp.PageCount()

	bandsWg.Wait()

	if len(errs) > 0 {
		return nil, 0, errs[0]
	}

	for _, b := range []*band{headerBand, footerBand} {
		if b == nil {
			continue
		}
		if b.multi && b.pages != totalPages {
			return nil, 0, fmt.Errorf("%s produced %d pages for a %d page document; reduce the %s content or increase its margin",
				b.spec.name, b.pages, totalPages, b.spec.name)
		}
		if err := comp.StampBandBytes(b.data, b.spec.placement, b.multi); err != nil {
			return nil, 0, err
		}
	}

	if watermarkBytes != nil {
		if err := comp.WatermarkBytes(watermarkBytes); err != nil {
			return nil, 0, err
		}
	}

	return comp, totalPages, nil
}

var pageTokenRe = regexp.MustCompile(`\{(PAGENO|TOPAGE)\}`)

func replacePageTokens(template string, pageNum, totalPages int) string {
	return pageTokenRe.ReplaceAllStringFunc(template, func(token string) string {
		if token == "{TOPAGE}" {
			return strconv.Itoa(totalPages)
		}
		return strconv.Itoa(pageNum)
	})
}

var (
	headRe        = regexp.MustCompile(`(?is)<head[^>]*>(.*?)</head>`)
	bodyRe        = regexp.MustCompile(`(?is)<body([^>]*)>(.*?)</body>`)
	doctypeRe     = regexp.MustCompile(`(?is)<!doctype[^>]*>`)
	htmlTagRe     = regexp.MustCompile(`(?is)</?html[^>]*>`)
	styleRe       = regexp.MustCompile(`(?is)<style[^>]*>(.*?)</style>`)
	htmlBodyTagRe = regexp.MustCompile(`(?i)(^|[\s,(])(html|body)([\s,.:#\[>~+)]|$)`)
	headOpenRe    = regexp.MustCompile(`(?i)<head[^>]*>`)
	htmlOpenRe    = regexp.MustCompile(`(?i)<html[^>]*>`)
)

func rewriteHtmlBodySelectors(headHTML string) string {
	return styleRe.ReplaceAllStringFunc(headHTML, func(block string) string {
		css := styleRe.FindStringSubmatch(block)[1]
		open := strings.Index(block, css)

		var out strings.Builder
		depth := 0
		selStart := 0
		for i := 0; i < len(css); i++ {
			switch css[i] {
			case '{':
				if depth == 0 {
					out.WriteString(htmlBodyTagRe.ReplaceAllString(css[selStart:i], "${1}.pdf-band-body${3}"))
					selStart = i
				}
				depth++
			case '}':
				depth--
				if depth == 0 {
					out.WriteString(css[selStart:i])
					selStart = i
				}
			}
		}
		out.WriteString(css[selStart:])

		return block[:open] + out.String() + block[open+len(css):]
	})
}

func buildPagedBandHTML(templateHTML string, totalPages int, heightInches float64) string {
	head := ""
	if m := headRe.FindStringSubmatch(templateHTML); m != nil {
		head = rewriteHtmlBodySelectors(m[1])
	}

	bodyAttrs, bodyInner := "", templateHTML
	if m := bodyRe.FindStringSubmatch(templateHTML); m != nil {
		bodyAttrs, bodyInner = strings.TrimSpace(m[1]), m[2]
	} else {
		bodyInner = headRe.ReplaceAllString(bodyInner, "")
		bodyInner = doctypeRe.ReplaceAllString(bodyInner, "")
		bodyInner = htmlTagRe.ReplaceAllString(bodyInner, "")
	}

	attrs := ""
	if bodyAttrs != "" {
		attrs = " " + bodyAttrs
	}

	var sb strings.Builder
	sb.Grow(len(bodyInner)*totalPages + len(head) + 512)

	sb.WriteString(`<!DOCTYPE html><html><head><meta charset="utf-8">`)
	sb.WriteString(head)
	fmt.Fprintf(&sb, `<style>
@page{margin:0;size:auto %.4fin}
*,*::before,*::after{box-sizing:border-box}
html,body{margin:0;padding:0}
.pdf-band-wrap{height:%.4fin;max-height:%.4fin;overflow:hidden;position:relative;margin:0;padding:0;break-after:page;page-break-after:always}
.pdf-band-wrap:last-child{break-after:auto;page-break-after:auto}
.pdf-band-body{height:100%%;max-height:100%%;overflow:hidden}
</style></head><body%s>`, heightInches, heightInches, heightInches, attrs)

	for p := 1; p <= totalPages; p++ {
		sb.WriteString(`<div class="pdf-band-wrap"><div class="pdf-band-body"` + attrs + `>`)
		sb.WriteString(replacePageTokens(bodyInner, p, totalPages))
		sb.WriteString("</div></div>")
	}

	sb.WriteString("</body></html>")
	return sb.String()
}

type bandSpec struct {
	name      string
	height    float64
	placement StampPlacement
}

type band struct {
	spec  bandSpec
	data  []byte
	multi bool
	pages int
}

func (j *job) renderBand(templateHTML string, totalPages int, spec bandSpec) (*band, error) {
	multi := pageTokenRe.MatchString(templateHTML)
	html := templateHTML
	if multi {
		html = buildPagedBandHTML(templateHTML, totalPages, spec.height)
	}

	data, err := j.renderer.RenderHTMLToPDFBytes(html, RenderOptions{
		PaperWidthInches:  j.geo.paperWidth,
		PaperHeightInches: spec.height,
		Scale:             1.0,
		Timeout:           j.timeout(),
	})
	if err != nil {
		j.log("Rendering %s... failed\n", spec.name)
		return nil, fmt.Errorf("failed to render %s: %w", spec.name, err)
	}

	pages := 0
	if multi {
		if pages, err = countPDFPages(data); err != nil {
			return nil, err
		}
	}
	j.log("Rendering %s... done\n", spec.name)

	return &band{spec: spec, data: data, multi: multi, pages: pages}, nil
}

func buildWatermarkHTML(html string, opacity float64) string {
	style := fmt.Sprintf(`<style>html,body{background:transparent !important;opacity:%.4f !important}</style>`, opacity)

	if headOpenRe.MatchString(html) {
		return headOpenRe.ReplaceAllStringFunc(html, func(m string) string { return m + style })
	}
	if htmlOpenRe.MatchString(html) {
		return htmlOpenRe.ReplaceAllStringFunc(html, func(m string) string { return m + "<head>" + style + "</head>" })
	}
	return `<!DOCTYPE html><html><head><meta charset="utf-8">` + style + `</head><body>` + html + `</body></html>`
}
