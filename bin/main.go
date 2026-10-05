package main

import (
	"errors"
	"flag"
	"fmt"
	"io"
	"os"
	"path/filepath"
)

var Version = "dev"

const usageText = `invoice-pdf - HTML to PDF engine for mikailfaruqali/invoice-template

Usage:
  invoice-pdf --content content.html --output invoice.pdf [options]

Input / output:
  --content <path>               Content HTML file, or "-" for stdin (required)
  --output <path>                Output PDF file, or "-" for stdout (required)
  --header-html <path>           Header HTML, drawn inside the top margin of every page
  --footer-html <path>           Footer HTML, drawn inside the bottom margin of every page
  --page-number-html <path>      Page number HTML, drawn in its own band at the bottom of every page
  --watermark-html <path>        Watermark HTML, stamped over every page
  --header-first-page-only       Draw the header on the first page only
  --footer-last-page-only        Draw the footer on the last page only

Page setup:
  --page-size <size>             A3, A4, A5, Letter or Legal (default: A4)
  --page-width <dim>             Explicit page width, used together with --page-height
  --page-height <dim>            Explicit page height
  --orientation <mode>           Portrait or Landscape (default: Portrait)
  --margin-top <dim>             Top margin, holds the header (default: 0)
  --margin-bottom <dim>          Bottom margin, holds the footer (default: 0)
  --margin-left <dim>            Left margin (default: 0)
  --margin-right <dim>           Right margin (default: 0)
  --header-spacing <dim>         Gap between the header and the content (default: 0)
  --footer-spacing <dim>         Gap between the content and the footer (default: 0)
  --page-number-height <dim>     Height of the page number band, added below margin-bottom (default: 8mm)
  --disable-smart-shrinking      Do not shrink content that is wider than the page
                                 (by default it is shrunk just enough to fit, down to 50%)
  --zoom <n>                     Content zoom, 0.1 - 2.0 (default: 1.0)
  --watermark-opacity <n>        Watermark opacity, 0.0 - 1.0 (default: 0.3)
  --copies <n>                   Place n copies of each page on one sheet when they fit; a short
                                 single-page document is stacked on its own paper (default: 1)
  --sheet-size <size>            Sheet the copies are placed on, A3, A4, A5, Letter or Legal (default: A4)

  Dimensions accept mm, cm, in, pt or px; a bare number is millimetres.
  {PAGENO} and {TOPAGE} in the header or footer become the page number and page count.

Behaviour:
  --chrome <path>                Chrome, Chromium or Edge executable (default: auto-detect)
  --timeout <seconds>            Per-render timeout (default: 120)
  --detect-chrome                Print the Chrome executable that will be used and exit
  --quiet, -q                    Suppress progress output
  --version, -v                  Print version and exit
  --help, -h                     Show this help
`

type config struct {
	contentFile, outputFile               string
	headerFile, footerFile, watermarkFile string
	pageNumberFile, pageNumberHeight      string
	sheetSize                             string
	copies                                int
	pageSize, pageWidth, pageHeight       string
	orientation                           string
	marginTop, marginBottom               string
	marginLeft, marginRight               string
	headerSpacing, footerSpacing          string
	disableSmartShrinking                 bool
	headerFirstPageOnly                   bool
	footerLastPageOnly                    bool
	zoom                                  float64
	watermarkOpacity                      float64
	chromePath                            string
	timeoutSeconds                        int
	quiet, showHelp, showVersion          bool
	detectChrome                          bool
}

func main() {
	if err := run(); err != nil {
		if errors.Is(err, flag.ErrHelp) {
			os.Exit(0)
		}
		fmt.Fprintf(os.Stderr, "invoice-pdf: %v\n", err)
		os.Exit(1)
	}
}

func parseFlags(cfg *config) error {
	fs := flag.NewFlagSet("invoice-pdf", flag.ContinueOnError)
	fs.SetOutput(io.Discard)

	fs.StringVar(&cfg.contentFile, "content", "", "")
	fs.StringVar(&cfg.outputFile, "output", "", "")
	fs.StringVar(&cfg.headerFile, "header-html", "", "")
	fs.StringVar(&cfg.footerFile, "footer-html", "", "")
	fs.StringVar(&cfg.watermarkFile, "watermark-html", "", "")
	fs.StringVar(&cfg.pageNumberFile, "page-number-html", "", "")
	fs.StringVar(&cfg.pageNumberHeight, "page-number-height", "8", "")
	fs.IntVar(&cfg.copies, "copies", 1, "")
	fs.StringVar(&cfg.sheetSize, "sheet-size", "A4", "")
	fs.StringVar(&cfg.pageSize, "page-size", "A4", "")
	fs.StringVar(&cfg.pageWidth, "page-width", "", "")
	fs.StringVar(&cfg.pageHeight, "page-height", "", "")
	fs.StringVar(&cfg.orientation, "orientation", "portrait", "")
	fs.StringVar(&cfg.marginTop, "margin-top", "0", "")
	fs.StringVar(&cfg.marginBottom, "margin-bottom", "0", "")
	fs.StringVar(&cfg.marginLeft, "margin-left", "0", "")
	fs.StringVar(&cfg.marginRight, "margin-right", "0", "")
	fs.StringVar(&cfg.headerSpacing, "header-spacing", "0", "")
	fs.StringVar(&cfg.footerSpacing, "footer-spacing", "0", "")
	fs.BoolVar(&cfg.disableSmartShrinking, "disable-smart-shrinking", false, "")
	fs.BoolVar(&cfg.headerFirstPageOnly, "header-first-page-only", false, "")
	fs.BoolVar(&cfg.footerLastPageOnly, "footer-last-page-only", false, "")
	fs.Float64Var(&cfg.zoom, "zoom", 1.0, "")
	fs.Float64Var(&cfg.watermarkOpacity, "watermark-opacity", 0.3, "")
	fs.StringVar(&cfg.chromePath, "chrome", "", "")
	fs.IntVar(&cfg.timeoutSeconds, "timeout", 120, "")
	fs.BoolVar(&cfg.detectChrome, "detect-chrome", false, "")
	fs.BoolVar(&cfg.quiet, "quiet", false, "")
	fs.BoolVar(&cfg.quiet, "q", false, "")
	fs.BoolVar(&cfg.showHelp, "help", false, "")
	fs.BoolVar(&cfg.showHelp, "h", false, "")
	fs.BoolVar(&cfg.showVersion, "version", false, "")
	fs.BoolVar(&cfg.showVersion, "v", false, "")

	if err := fs.Parse(os.Args[1:]); err != nil {
		fmt.Fprint(os.Stderr, usageText)
		return err
	}
	if extra := fs.Args(); len(extra) > 0 {
		return fmt.Errorf("unexpected argument %q (all options use --flag form)", extra[0])
	}
	return nil
}

func readHTMLInput(path string) (string, error) {
	if path == "" {
		return "", nil
	}

	var (
		data []byte
		err  error
	)
	if path == "-" {
		data, err = io.ReadAll(os.Stdin)
	} else {
		data, err = os.ReadFile(path)
	}
	if err != nil {
		return "", fmt.Errorf("failed to read '%s': %w", path, err)
	}
	return string(data), nil
}

func run() error {
	var cfg config
	if err := parseFlags(&cfg); err != nil {
		return err
	}

	if cfg.showHelp {
		fmt.Fprint(os.Stderr, usageText)
		return nil
	}
	if cfg.showVersion {
		fmt.Println("invoice-pdf " + Version)
		return nil
	}
	if cfg.detectChrome {
		chromeBin, err := DetectChromeBinary(cfg.chromePath)
		if err != nil {
			return err
		}
		fmt.Println(chromeBin)
		return nil
	}
	if cfg.contentFile == "" || cfg.outputFile == "" {
		fmt.Fprint(os.Stderr, usageText)
		return errors.New("--content and --output are required")
	}
	if cfg.zoom < 0.1 || cfg.zoom > 2.0 {
		return fmt.Errorf("--zoom must be between 0.1 and 2.0, got %g", cfg.zoom)
	}
	if cfg.watermarkOpacity < 0 || cfg.watermarkOpacity > 1 {
		return fmt.Errorf("--watermark-opacity must be between 0.0 and 1.0, got %g", cfg.watermarkOpacity)
	}
	if cfg.timeoutSeconds <= 0 {
		return fmt.Errorf("--timeout must be positive, got %d", cfg.timeoutSeconds)
	}

	geo, err := resolveGeometry(&cfg)
	if err != nil {
		return err
	}

	chromeBin, err := DetectChromeBinary(cfg.chromePath)
	if err != nil {
		return err
	}

	renderer := NewChromeRenderer(chromeBin)
	defer renderer.Close()

	go renderer.Start()

	html := make([]string, 5)
	for i, path := range []string{cfg.contentFile, cfg.headerFile, cfg.footerFile, cfg.pageNumberFile, cfg.watermarkFile} {
		if html[i], err = readHTMLInput(path); err != nil {
			return err
		}
	}
	if html[0] == "" {
		return errors.New("content HTML is empty")
	}

	logf := func(format string, args ...interface{}) {
		if !cfg.quiet {
			fmt.Fprintf(os.Stderr, format, args...)
		}
	}

	j := &job{cfg: &cfg, geo: geo, renderer: renderer, logf: logf, html: [5]string(html)}

	comp, totalPages, err := j.build(html[0], html[1], html[2], html[3], html[4])
	if err != nil {
		return err
	}

	if cfg.copies > 1 {
		if comp, err = j.impose(comp, totalPages); err != nil {
			return err
		}
	}

	if err := writeOutput(comp, cfg.outputFile); err != nil {
		return err
	}

	logf("Generated %d page(s)\n", totalPages)
	return nil
}

func writeOutput(comp *Composer, outputFile string) error {
	if outputFile == "-" {
		if err := comp.Write(os.Stdout); err != nil {
			return fmt.Errorf("failed to write PDF to stdout: %w", err)
		}
		return nil
	}

	if dir := filepath.Dir(outputFile); dir != "" && dir != "." {
		if err := os.MkdirAll(dir, 0755); err != nil {
			return fmt.Errorf("failed to create output directory '%s': %w", dir, err)
		}
	}

	f, err := os.OpenFile(outputFile, os.O_WRONLY|os.O_CREATE|os.O_TRUNC, 0644)
	if err != nil {
		return fmt.Errorf("failed to create output file '%s': %w", outputFile, err)
	}
	defer f.Close()

	if err := comp.Write(f); err != nil {
		return fmt.Errorf("failed to write '%s': %w", outputFile, err)
	}
	return nil
}
