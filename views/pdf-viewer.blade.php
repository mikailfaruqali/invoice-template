<!DOCTYPE html>
<html dir="{{ $dir }}" data-theme="{{ $theme }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="{{ $theme }}" />
    <title>{{ $title }}</title>
    @if (filled($favicon))
        <link rel="icon" href="{{ $favicon }}" />
    @endif
    @if (filled($font))
        <style>
            @font-face {
                font-family: '{{ $fontFamily }}';
                src: url('data:font/truetype;base64,{{ $font }}') format('truetype');
                font-weight: normal;
                font-style: normal;
            }
        </style>
    @endif

    <style>
        :root {
            color-scheme: light;
            --viewer-bg: #f5f6fa;
            --nav-height: 56px;
            --nav-cap: 6px;
            --nav-bg: #ffffff;
            --nav-line: #e5e9f2;
            --nav-text: #364a63;
            --nav-muted: #6e7f99;
            --nav-surface: #f5f6fa;
            --nav-surface-strong: #ebeef4;
            --card-radius: 4px;
            --card-shadow: rgba(0, 0, 0, 0.16) 0 1px 4px;
            --scrollbar: #cdd3e0;
            --scrollbar-hover: #b7c2d0;
            --spinner-track: #dbdfea;
            --primary: #6576ff;
            --danger: #e85347;
            --close-fg: #dc2626;
            --print-fg: #16a34a;
            --download-fg: #2563eb;
            --share-fg: #ea580c;
        }

        :root[data-theme='dark'] {
            color-scheme: dark;
            --viewer-bg: #0d141d;
            --nav-bg: #141c26;
            --nav-line: #3d444d;
            --nav-text: #e9eefb;
            --nav-muted: #8f9fbb;
            --nav-surface: rgba(255, 255, 255, 0.05);
            --nav-surface-strong: rgba(255, 255, 255, 0.09);
            --card-shadow: rgba(0, 0, 0, 0.19) 0 10px 20px, rgba(0, 0, 0, 0.23) 0 6px 6px;
            --scrollbar: rgba(139, 148, 158, 0.4);
            --scrollbar-hover: #3c4d62;
            --spinner-track: #3d444d;
            --close-fg: #f87171;
            --print-fg: #4ade80;
            --download-fg: #60a5fa;
            --share-fg: #fb923c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            overflow: hidden;
            font-family: {!! $fontStack !!};
            background: var(--viewer-bg);
        }

        #toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--nav-height);
            z-index: 999;
            background: var(--nav-bg);
            border-bottom: 1px solid var(--nav-line);
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 16px;
        }

        #toolbar::after {
            content: '';
            position: absolute;
            top: calc(100% + 1px);
            left: 0;
            right: 0;
            height: var(--nav-cap);
            background: var(--viewer-bg);
            pointer-events: none;
        }

        #doc-title {
            color: var(--nav-text);
            font-size: 15px;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
            min-width: 0;
            unicode-bidi: plaintext;
            text-align: start;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            margin-inline-start: auto;
        }

        .btn {
            position: relative;
            width: 40px;
            height: 40px;
            border: 1px solid var(--nav-line);
            border-radius: 50%;
            background: var(--nav-surface);
            color: var(--nav-muted);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            outline: none;
            transition:
                background 0.18s ease,
                border-color 0.18s ease,
                color 0.18s ease,
                transform 0.1s;
        }

        .btn:hover,
        .btn:focus-visible {
            background: var(--nav-surface-strong);
        }

        .btn:active {
            transform: scale(0.94);
        }

        .btn svg {
            width: 19px;
            height: 19px;
            stroke-width: 1.8;
            display: block;
            pointer-events: none;
        }

        .btn-print {
            display: none;
            color: var(--print-fg);
        }

        .btn-print.is-supported {
            display: inline-flex;
        }

        .btn-download {
            color: var(--download-fg);
        }

        .btn-share {
            color: var(--share-fg);
        }

        #pdf-viewer-close-btn {
            color: var(--close-fg);
            animation: pulse-once 0.6s ease-out 0.3s both;
        }

        @keyframes pulse-once {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.45);
            }

            50% {
                transform: scale(1.08);
                box-shadow: 0 0 0 8px rgba(220, 38, 38, 0);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0);
            }
        }

        #pdf-container {
            position: fixed;
            top: calc(var(--nav-height) + var(--nav-cap));
            left: 0;
            right: 0;
            bottom: 0;
            overflow-y: auto;
            overflow-x: hidden;
            overscroll-behavior: contain;
            background: var(--viewer-bg);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px 12px 16px;
            gap: 12px;
            scrollbar-width: thin;
            scrollbar-color: var(--scrollbar) transparent;
        }

        #pdf-container::-webkit-scrollbar {
            width: 6px;
        }

        #pdf-container::-webkit-scrollbar-track {
            background: transparent;
        }

        #pdf-container::-webkit-scrollbar-thumb {
            background: var(--scrollbar);
            border-radius: 4px;
        }

        #pdf-container::-webkit-scrollbar-thumb:hover {
            background: var(--scrollbar-hover);
        }

        .pdf-page {
            display: block;
            flex-shrink: 0;
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
        }

        #loading,
        #error-screen {
            position: fixed;
            top: calc(var(--nav-height) + 1px);
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--viewer-bg);
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        #loading {
            display: flex;
            gap: 16px;
            z-index: 10;
        }

        #loading-spinner {
            width: 28px;
            height: 28px;
            border: 2px solid var(--spinner-track);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        #loading-text {
            color: var(--nav-muted);
            font-size: 12px;
            letter-spacing: 0.04em;
        }

        #error-screen {
            display: none;
            gap: 8px;
            color: var(--danger);
            font-size: 13px;
        }

        @media (max-width: 767px) {
            #toolbar {
                padding: 0 12px;
                gap: 8px;
            }

            .actions {
                gap: 4px;
            }

            .btn {
                width: 36px;
                height: 36px;
            }

            .btn svg {
                width: 17px;
                height: 17px;
            }
        }
    </style>
</head>

<body>
    <div id="toolbar">
        <button id="pdf-viewer-close-btn" class="btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>

        <span id="doc-title">{{ $title }}</span>

        <div class="actions">
            <button id="btn-print" class="btn btn-print">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
            </button>

            <button id="btn-download" class="btn btn-download">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
            </button>

            <button id="btn-share" class="btn btn-share">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <circle cx="18" cy="5" r="3" />
                    <circle cx="6" cy="12" r="3" />
                    <circle cx="18" cy="19" r="3" />
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                </svg>
            </button>
        </div>
    </div>

    <div id="loading">
        <div id="loading-spinner"></div>
        <div id="loading-text">Loading…</div>
    </div>

    <div id="error-screen">Failed to load document.</div>

    <div id="pdf-container"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        var CMAP_URL = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/';
        var STANDARD_FONT = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/standard_fonts/';

        var PdfViewerState = {
            filename: @json($filename),
            blob: null,
            url: null,
            pdfDoc: null,
            renderId: 0,
            renderedWidth: null,
            renderedDpr: null,
        };

        var DocumentElements = {
            get container() {
                return document.getElementById('pdf-container');
            },
            get loading() {
                return document.getElementById('loading');
            },
            get loadingText() {
                return document.getElementById('loading-text');
            },
            get errorScreen() {
                return document.getElementById('error-screen');
            },
            get btnPrint() {
                return document.getElementById('btn-print');
            },
            get btnDownload() {
                return document.getElementById('btn-download');
            },
            get btnShare() {
                return document.getElementById('btn-share');
            },
            get btnClose() {
                return document.getElementById('pdf-viewer-close-btn');
            },
        };

        var PdfDecoder = {
            fromBase64: function (b64) {
                var binary = atob(b64);
                var bytes = new Uint8Array(binary.length);
                for (var i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
                return bytes.buffer;
            },
            toBlob: function (buf) {
                return new Blob([buf], {
                    type: 'application/pdf',
                });
            },
            toObjectUrl: function (blob) {
                return URL.createObjectURL(blob);
            },
        };

        var PdfRenderer = {
            getDisplayWidth: function () {
                var container = DocumentElements.container;
                var availableWidth = (container ? container.clientWidth : window.innerWidth) || window.innerWidth;
                var isMobile = window.innerWidth < 768;

                if (isMobile) {
                    return Math.max(availableWidth - 24, 280);
                }

                return Math.min(availableWidth - 48, 880);
            },

            getDevicePixelRatio: function () {
                return window.devicePixelRatio || 1;
            },

            snapToDevicePixels: function (size, dpr, downOnly) {
                var base = downOnly ? Math.floor(size) : Math.round(size);
                var best = base;
                var bestError = Infinity;

                for (var offset = 0; offset < 8; offset++) {
                    var candidates = downOnly ? [base - offset] : [base - offset, base + offset];

                    for (var i = 0; i < candidates.length; i++) {
                        var candidate = candidates[i];
                        if (candidate <= 0) continue;

                        var device = candidate * dpr;
                        var error = Math.abs(device - Math.round(device));

                        if (error < bestError - 1e-6) {
                            best = candidate;
                            bestError = error;
                        }
                    }

                    if (bestError < 0.01) break;
                }

                return best;
            },

            renderPage: function (num, renderId) {
                return PdfViewerState.pdfDoc.getPage(num).then(function (page) {
                    if (renderId !== PdfViewerState.renderId) return;

                    var dpr = PdfRenderer.getDevicePixelRatio();
                    var baseViewport = page.getViewport({ scale: 1.0 });

                    var cssWidth = PdfRenderer.snapToDevicePixels(PdfRenderer.getDisplayWidth(), dpr, true);
                    var cssHeight = PdfRenderer.snapToDevicePixels(
                        (cssWidth * baseViewport.height) / baseViewport.width,
                        dpr,
                        false,
                    );

                    var pixelWidth = Math.round(cssWidth * dpr);
                    var pixelHeight = Math.round(cssHeight * dpr);

                    var viewport = page.getViewport({
                        scale: cssWidth / baseViewport.width,
                    });

                    var canvas = document.createElement('canvas');
                    canvas.className = 'pdf-page';
                    canvas.width = pixelWidth;
                    canvas.height = pixelHeight;
                    canvas.style.width = cssWidth + 'px';
                    canvas.style.height = cssHeight + 'px';

                    DocumentElements.container.appendChild(canvas);

                    var ctx = canvas.getContext('2d', {
                        alpha: false,
                    });

                    return page.render({
                        canvasContext: ctx,
                        viewport: viewport,
                        transform: [pixelWidth / viewport.width, 0, 0, pixelHeight / viewport.height, 0, 0],
                        intent: 'display',
                    }).promise;
                });
            },

            renderAll: function () {
                var total = PdfViewerState.pdfDoc.numPages;
                var renderId = ++PdfViewerState.renderId;
                var promise = Promise.resolve();

                PdfViewerState.renderedWidth = PdfRenderer.getDisplayWidth();
                PdfViewerState.renderedDpr = PdfRenderer.getDevicePixelRatio();
                DocumentElements.container.innerHTML = '';

                for (var i = 1; i <= total; i++) {
                    (function (num) {
                        promise = promise.then(function () {
                            if (renderId !== PdfViewerState.renderId) return;
                            DocumentElements.loadingText.textContent = 'Page ' + num + ' of ' + total;
                            return PdfRenderer.renderPage(num, renderId);
                        });
                    })(i);
                }

                return promise;
            },

            watchResize: function () {
                var timer = null;

                window.addEventListener('resize', function () {
                    clearTimeout(timer);
                    timer = setTimeout(function () {
                        if (
                            PdfRenderer.getDisplayWidth() === PdfViewerState.renderedWidth &&
                            PdfRenderer.getDevicePixelRatio() === PdfViewerState.renderedDpr
                        ) {
                            return;
                        }

                        PdfRenderer.renderAll();
                    }, 250);
                });
            },
        };

        var PdfActions = {
            download: function () {
                var a = document.createElement('a');
                a.href = PdfViewerState.url;
                a.download = PdfViewerState.filename;
                a.click();
            },

            print: function () {
                var iframe = document.createElement('iframe');
                iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:1px;height:1px;visibility:hidden;';
                iframe.src = PdfViewerState.url;
                document.body.appendChild(iframe);
                iframe.onload = function () {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                    setTimeout(function () {
                        document.body.removeChild(iframe);
                    }, 60000);
                };
            },

            share: function () {
                var file = new File([PdfViewerState.blob], PdfViewerState.filename, {
                    type: 'application/pdf',
                });

                if (
                    navigator.canShare &&
                    navigator.canShare({
                        files: [file],
                    })
                ) {
                    navigator
                        .share({
                            files: [file],
                            title: PdfViewerState.filename,
                        })
                        .catch(function (e) {
                            if (e.name !== 'AbortError') console.error('Share failed', e);
                        });
                    return;
                }

                navigator.clipboard.writeText(location.href);
            },

            close: function () {
                if (window.self !== window.top) {
                    window.parent.$('.modal').modal('hide');
                } else {
                    window.close();
                }
            },
        };

        var PdfViewer = {
            initialize: function () {
                var base64 = @json($base64);
                var buffer = PdfDecoder.fromBase64(base64);

                PdfViewerState.blob = PdfDecoder.toBlob(buffer);
                PdfViewerState.url = PdfDecoder.toObjectUrl(PdfViewerState.blob);

                pdfjsLib
                    .getDocument({
                        data: buffer,
                        cMapUrl: CMAP_URL,
                        cMapPacked: true,
                        standardFontDataUrl: STANDARD_FONT,
                        disableFontFace: true,
                    })
                    .promise.then(function (pdfDoc) {
                        PdfViewerState.pdfDoc = pdfDoc;
                        return PdfRenderer.renderAll();
                    })
                    .then(function () {
                        DocumentElements.loading.style.display = 'none';
                        PdfRenderer.watchResize();
                        DocumentElements.btnPrint.addEventListener('click', PdfActions.print);
                        DocumentElements.btnDownload.addEventListener('click', PdfActions.download);
                        DocumentElements.btnShare.addEventListener('click', PdfActions.share);
                    })
                    .catch(function (err) {
                        console.error('PDF error', err);
                        DocumentElements.loading.style.display = 'none';
                        DocumentElements.errorScreen.style.display = 'flex';
                    });
            },
        };

        var Application = {
            initialize: function () {
                PdfViewer.initialize();
                ApplicationUI.setupPrintButton();
                ApplicationUI.setupShareButton();
            },
        };

        var ApplicationUI = {
            setupPrintButton: function () {
                if (ApplicationUI.canPrintDirectly()) {
                    DocumentElements.btnPrint.classList.add('is-supported');
                }
            },

            canPrintDirectly: function () {
                var ua = navigator.userAgent || '';
                var uaData = navigator.userAgentData;

                if (typeof window.print !== 'function') return false;
                if (uaData && uaData.mobile) return false;
                if (/Android|iPhone|iPad|iPod|Mobile|webOS|BlackBerry|IEMobile|Opera Mini|Silk|Kindle|wv\)/i.test(ua)) return false;
                if (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1) return false;
                if (/^((?!chrome|chromium|crios|fxios|edg|opr|android).)*safari/i.test(ua)) return false;
                if (navigator.pdfViewerEnabled === false) return false;
                if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches && !window.matchMedia('(any-pointer: fine)').matches) return false;

                return true;
            },

            setupShareButton: function () {
                if (!navigator.share || !navigator.canShare) {
                    DocumentElements.btnShare.style.display = 'none';
                    return;
                }

                try {
                    var testFile = new File([], 'test.pdf', { type: 'application/pdf' });
                    if (!navigator.canShare({ files: [testFile] })) {
                        DocumentElements.btnShare.style.display = 'none';
                    }
                } catch (e) {
                    DocumentElements.btnShare.style.display = 'none';
                }
            },
        };

        document.addEventListener('DOMContentLoaded', function () {
            Application.initialize();
            DocumentElements.btnClose.addEventListener('click', PdfActions.close);
        });
    </script>
</body>
</html>
