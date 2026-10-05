package main

import (
	"context"
	"encoding/base64"
	"fmt"
	"io"
	"math"
	"net"
	"net/http"
	"net/url"
	"path/filepath"
	"regexp"
	"strings"
	"sync"
	"time"

	"github.com/chromedp/cdproto"
	"github.com/chromedp/cdproto/cdp"
	"github.com/chromedp/cdproto/emulation"
	cdpio "github.com/chromedp/cdproto/io"
	"github.com/chromedp/cdproto/page"
	"github.com/chromedp/cdproto/runtime"
	"github.com/chromedp/chromedp"
)

type ChromeRenderer struct {
	chromePath  string
	allocCtx    context.Context
	allocCancel context.CancelFunc

	browserCtx    context.Context
	browserCancel context.CancelFunc
	browserOnce   sync.Once
	browserErr    error

	server     *assetServer
	serverOnce sync.Once
	serverErr  error
}

func NewChromeRenderer(chromePath string) *ChromeRenderer {
	opts := append(chromedp.DefaultExecAllocatorOptions[:],
		chromedp.ExecPath(chromePath),
		chromedp.NoSandbox,
		chromedp.DisableGPU,
		chromedp.Flag("disable-background-networking", true),
		chromedp.Flag("disable-default-apps", true),
		chromedp.Flag("disable-extensions", true),
		chromedp.Flag("disable-sync", true),
		chromedp.Flag("disable-translate", true),
		chromedp.Flag("headless", true),
		chromedp.Flag("hide-scrollbars", true),
		chromedp.Flag("metrics-recording-only", true),
		chromedp.Flag("mute-audio", true),
		chromedp.Flag("no-first-run", true),
		chromedp.Flag("safebrowsing-disable-auto-update", true),
		chromedp.Flag("font-render-hinting", "none"),
		chromedp.Flag("disable-breakpad", true),
		chromedp.Flag("disable-component-update", true),
		chromedp.Flag("disable-domain-reliability", true),
		chromedp.Flag("disable-client-side-phishing-detection", true),
		chromedp.Flag("disable-ipc-flooding-protection", true),
		chromedp.Flag("no-default-browser-check", true),
		chromedp.Flag("no-pings", true),
		chromedp.Flag("password-store", "basic"),
		chromedp.Flag("use-mock-keychain", true),
		chromedp.Flag("disable-features", "Translate,OptimizationHints,MediaRouter,DialLocalDiscovery"),
		chromedp.Flag("disable-background-timer-throttling", true),
		chromedp.Flag("disable-backgrounding-occluded-windows", true),
		chromedp.Flag("disable-renderer-backgrounding", true),
		chromedp.Flag("run-all-compositor-stages-before-draw", true),
		chromedp.Flag("disable-software-rasterizer", true),
		chromedp.Flag("disable-lcd-text", true),
		chromedp.Flag("disable-dev-shm-usage", true),
		chromedp.Flag("disable-hang-monitor", true),
		chromedp.Flag("disable-back-forward-cache", true),
		chromedp.Flag("disable-logging", true),
		chromedp.Flag("log-level", "3"),
	)

	allocCtx, allocCancel := chromedp.NewExecAllocator(context.Background(), opts...)
	return &ChromeRenderer{
		chromePath:  chromePath,
		allocCtx:    allocCtx,
		allocCancel: allocCancel,
	}
}

func discard(string, ...interface{}) {}

func (cr *ChromeRenderer) Start() error { return cr.ensureBrowser() }

func (cr *ChromeRenderer) ensureServer() error {
	cr.serverOnce.Do(func() {
		cr.server, cr.serverErr = newAssetServer()
	})
	return cr.serverErr
}

func (cr *ChromeRenderer) ensureBrowser() error {
	cr.browserOnce.Do(func() {
		ctx, cancel := chromedp.NewContext(cr.allocCtx, chromedp.WithLogf(discard), chromedp.WithErrorf(discard))
		if err := chromedp.Run(ctx); err != nil {
			cancel()
			cr.browserErr = fmt.Errorf("failed to start Chrome at %s: %w", cr.chromePath, err)
			return
		}
		cr.browserCtx = ctx
		cr.browserCancel = cancel
	})
	return cr.browserErr
}

func (cr *ChromeRenderer) Close() {
	if cr.browserCancel != nil {
		cr.browserCancel()
	}
	if cr.allocCancel != nil {
		cr.allocCancel()
	}
	if cr.server != nil {
		cr.server.Close()
	}
}

type RenderOptions struct {
	PaperWidthInches   float64
	PaperHeightInches  float64
	MarginTopInches    float64
	MarginBottomInches float64
	MarginLeftInches   float64
	MarginRightInches  float64
	Landscape          bool
	Scale              float64
	SmartShrink        bool
	Timeout            time.Duration
}

var (
	attrURLRe   = regexp.MustCompile(`(?i)(\b(?:src|href)\s*=\s*)("[^"]*"|'[^']*')`)
	cssURLRe    = regexp.MustCompile(`(?i)url\(\s*("[^"]*"|'[^']*'|[^)"'\s]+)\s*\)`)
	drivePathRe = regexp.MustCompile(`^[A-Za-z]:[\\/]`)
)

type assetServer struct {
	listener net.Listener
	srv      *http.Server
	origin   string

	mu    sync.Mutex
	pages map[string]string
	files map[string]string
	seq   int
}

func newAssetServer() (*assetServer, error) {
	ln, err := net.Listen("tcp", "127.0.0.1:0")
	if err != nil {
		return nil, fmt.Errorf("failed to start local asset server: %w", err)
	}
	as := &assetServer{
		listener: ln,
		pages:    map[string]string{},
		files:    map[string]string{},
		origin:   "http://" + ln.Addr().String(),
	}
	mux := http.NewServeMux()
	mux.HandleFunc("/", as.handle)
	as.srv = &http.Server{Handler: mux}
	go as.srv.Serve(ln)
	return as, nil
}

func (as *assetServer) Close() {
	if as.srv != nil {
		ctx, cancel := context.WithTimeout(context.Background(), 2*time.Second)
		defer cancel()
		as.srv.Shutdown(ctx)
	}
}

func (as *assetServer) register(html string) string {
	html = as.localize(html)

	as.mu.Lock()
	defer as.mu.Unlock()
	as.seq++
	token := fmt.Sprintf("d%d", as.seq)
	as.pages[token] = html
	return fmt.Sprintf("%s/%s/", as.origin, token)
}

func (as *assetServer) localize(html string) string {
	html = attrURLRe.ReplaceAllStringFunc(html, func(match string) string {
		groups := attrURLRe.FindStringSubmatch(match)
		quote := groups[2][:1]
		if local, ok := as.localURL(groups[2][1 : len(groups[2])-1]); ok {
			return groups[1] + quote + local + quote
		}
		return match
	})

	return cssURLRe.ReplaceAllStringFunc(html, func(match string) string {
		groups := cssURLRe.FindStringSubmatch(match)
		if local, ok := as.localURL(strings.Trim(groups[1], `"'`)); ok {
			return `url("` + local + `")`
		}
		return match
	})
}

func (as *assetServer) localURL(value string) (string, bool) {
	path, ok := localPath(strings.TrimSpace(value))
	if !ok {
		return "", false
	}

	key := base64.RawURLEncoding.EncodeToString([]byte(path))

	as.mu.Lock()
	as.files[key] = path
	as.mu.Unlock()

	return as.origin + "/__local/" + key + "/" + url.PathEscape(filepath.Base(path)), true
}

func localPath(value string) (string, bool) {
	var path string

	switch {
	case strings.HasPrefix(strings.ToLower(value), "file:"):
		parsed, err := url.Parse(value)
		if err != nil || parsed.Path == "" {
			return "", false
		}
		path = parsed.Path
		if drivePathRe.MatchString(strings.TrimPrefix(path, "/")) {
			path = strings.TrimPrefix(path, "/")
		}
	case drivePathRe.MatchString(value):
		path = value
	case strings.HasPrefix(value, "/") && !strings.HasPrefix(value, "//"):
		path = value
	default:
		return "", false
	}

	if i := strings.IndexAny(path, "?#"); i >= 0 {
		path = path[:i]
	}
	return filepath.FromSlash(path), true
}

func (as *assetServer) handle(w http.ResponseWriter, r *http.Request) {
	token, rest, _ := strings.Cut(strings.TrimPrefix(r.URL.Path, "/"), "/")

	if token == "__local" {
		key, _, _ := strings.Cut(rest, "/")

		as.mu.Lock()
		path, ok := as.files[key]
		as.mu.Unlock()

		if !ok {
			http.NotFound(w, r)
			return
		}
		w.Header().Set("Cache-Control", "no-store")
		http.ServeFile(w, r, path)
		return
	}

	as.mu.Lock()
	html, ok := as.pages[token]
	as.mu.Unlock()

	if !ok || rest != "" {
		http.NotFound(w, r)
		return
	}

	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	w.Header().Set("Cache-Control", "no-store")
	io.WriteString(w, html)
}

func (cr *ChromeRenderer) RenderHTMLToPDFBytes(htmlContent string, opts RenderOptions) ([]byte, error) {
	return cr.renderPDF(htmlContent, opts, nil)
}

func (cr *ChromeRenderer) RenderHTMLToPDFBytesCounted(htmlContent string, opts RenderOptions, pageCount chan<- int) ([]byte, error) {
	return cr.renderPDF(htmlContent, opts, pageCount)
}

func (cr *ChromeRenderer) renderPDF(htmlContent string, opts RenderOptions, pageCount chan<- int) ([]byte, error) {
	counted := false
	emit := func(n int) {
		if pageCount != nil && !counted {
			counted = true
			pageCount <- n
		}
	}
	defer func() {
		if pageCount != nil && !counted {
			close(pageCount)
		}
	}()

	if err := cr.ensureBrowser(); err != nil {
		return nil, err
	}
	if err := cr.ensureServer(); err != nil {
		return nil, err
	}

	timeout := opts.Timeout
	if timeout <= 0 {
		timeout = 120 * time.Second
	}

	navURL := cr.server.register(htmlContent)

	ctx, cancel := chromedp.NewContext(cr.browserCtx)
	defer cancel()

	ctx, cancelTimeout := context.WithTimeout(ctx, timeout)
	defer cancelTimeout()

	zoom := opts.Scale
	if zoom <= 0 {
		zoom = 1.0
	}

	var pdfBuf []byte
	err := chromedp.Run(ctx,
		emulation.SetEmulatedMedia().WithMedia("print"),
		chromedp.Navigate(navURL),
		chromedp.WaitReady("body", chromedp.ByQuery),
		chromedp.ActionFunc(waitForAssets),
		chromedp.ActionFunc(func(ctx context.Context) error {
			if !opts.SmartShrink {
				return nil
			}
			factor, err := smartShrinkFactor(ctx, opts)
			if err != nil {
				return err
			}
			zoom = min(max(zoom*factor, 0.1), 2.0)
			return nil
		}),
		chromedp.ActionFunc(func(ctx context.Context) error {
			script := fmt.Sprintf(`(() => {
  const zoom = %f;
  if (zoom !== 1) {
    document.documentElement.style.setProperty('zoom', String(zoom), 'important');
  }
  document.querySelectorAll('[data-band-spacer]').forEach((el) => {
    el.style.height = (parseFloat(el.dataset.bandSpacer) / zoom) + 'in';
  });
})()`, zoom)
			return chromedp.Evaluate(script, nil).Do(ctx)
		}),
		chromedp.ActionFunc(func(ctx context.Context) error {
			data, stream, err := page.PrintToPDF().
				WithPrintBackground(true).
				WithPaperWidth(opts.PaperWidthInches).
				WithPaperHeight(opts.PaperHeightInches).
				WithMarginTop(opts.MarginTopInches).
				WithMarginBottom(opts.MarginBottomInches).
				WithMarginLeft(opts.MarginLeftInches).
				WithMarginRight(opts.MarginRightInches).
				WithLandscape(opts.Landscape).
				WithScale(1).
				WithDisplayHeaderFooter(false).
				WithTransferMode(page.PrintToPDFTransferModeReturnAsStream).
				Do(ctx)
			if err != nil {
				return err
			}

			if stream != "" {
				defer cdpio.Close(stream).Do(ctx)
				if data, err = readStream(ctx, stream); err != nil {
					return err
				}
			}

			n, err := countPDFPages(data)
			if err != nil {
				return err
			}
			pdfBuf = data
			emit(n)
			return nil
		}),
	)
	if err != nil {
		return nil, fmt.Errorf("chromedp render error: %w", err)
	}
	if len(pdfBuf) == 0 {
		return nil, fmt.Errorf("chrome produced an empty PDF")
	}

	return pdfBuf, nil
}

var pageObjRe = regexp.MustCompile(`/Type\s*/Page[^s]`)

func countPDFPages(data []byte) (int, error) {
	if n := len(pageObjRe.FindAll(data, -1)); n > 0 {
		return n, nil
	}
	return GetPDFPageCountFromBytes(data)
}

func readStream(ctx context.Context, handle cdpio.StreamHandle) ([]byte, error) {
	var out []byte
	for {
		var res cdpio.ReadReturns
		params := cdpio.Read(handle).WithSize(10 << 20)
		if err := cdp.Execute(ctx, cdproto.CommandIORead, params, &res); err != nil {
			return nil, fmt.Errorf("failed to read PDF stream: %w", err)
		}
		if res.Base64encoded {
			decoded, err := base64.StdEncoding.DecodeString(res.Data)
			if err != nil {
				return nil, fmt.Errorf("failed to decode PDF stream chunk: %w", err)
			}
			out = append(out, decoded...)
		} else {
			out = append(out, res.Data...)
		}
		if res.EOF {
			return out, nil
		}
	}
}

const cssPixelsPerInch = 96.0

const (
	minSmartShrinkFactor = 1.25
	maxSmartShrinkFactor = 2.0
)

func smartShrinkFactor(ctx context.Context, opts RenderOptions) (float64, error) {
	printablePx := (opts.PaperWidthInches - opts.MarginLeftInches - opts.MarginRightInches) * cssPixelsPerInch
	if printablePx <= 0 {
		return 1.0, nil
	}

	minWidth := printablePx * minSmartShrinkFactor
	maxWidth := printablePx * maxSmartShrinkFactor
	height := int64(math.Round(opts.PaperHeightInches * cssPixelsPerInch))

	if err := emulation.SetDeviceMetricsOverride(int64(math.Round(minWidth)), height, 1, false).Do(ctx); err != nil {
		return 1.0, err
	}
	defer emulation.ClearDeviceMetricsOverride().Do(ctx)

	const script = `(() => {
  let left = 0;
  let right = window.innerWidth;
  for (const el of document.body ? document.body.querySelectorAll('*') : []) {
    const rect = el.getBoundingClientRect();
    if (rect.width > 0 && rect.height > 0) {
      left = Math.min(left, rect.left);
      right = Math.max(right, rect.right);
    }
  }
  return Math.max(right - left, document.documentElement.scrollWidth || 0, document.body ? document.body.scrollWidth : 0);
})()`

	var widest float64
	if err := chromedp.Evaluate(script, &widest).Do(ctx); err != nil {
		return 1.0, err
	}

	layoutWidth := min(max(widest, minWidth), maxWidth)

	return printablePx / layoutWidth, nil
}

func waitForAssets(ctx context.Context) error {
	const script = `
new Promise((resolve) => {
  const onReady = () => {
    if (window.requestAnimationFrame) {
      requestAnimationFrame(() => resolve(true));
    } else {
      resolve(true);
    }
  };
  const done = () => {
    const imgs = Array.from(document.images || []);
    const pending = imgs.filter((i) => !i.complete);
    if (pending.length === 0) {
      onReady();
      return;
    }
    let left = pending.length;
    const tick = () => {
      if (--left <= 0) onReady();
    };
    pending.forEach((i) => {
      i.addEventListener('load', tick, { once: true });
      i.addEventListener('error', tick, { once: true });
    });
  };
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(done).catch(done);
  } else {
    done();
  }
})`
	var ok bool
	waitCtx, cancel := context.WithTimeout(ctx, 20*time.Second)
	defer cancel()
	_ = chromedp.Evaluate(script, &ok, func(p *runtime.EvaluateParams) *runtime.EvaluateParams {
		return p.WithAwaitPromise(true)
	}).Do(waitCtx)
	return nil
}
