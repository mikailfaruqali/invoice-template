<!DOCTYPE html>
<html dir="{{ $dir }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        @font-face {
            font-family: 'Vazirmatn';
            src: url('data:font/truetype;base64,{{ $font }}') format('truetype');
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            height: 100vh;
            overflow: hidden;
            font-family: 'Vazirmatn', system-ui, sans-serif;
            background: #1e1e1e;
        }

        #toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 52px;
            z-index: 999;
            background: #161616;
            border-bottom: 1px solid #2a2a2a;
            display: flex;
            align-items: center;
            padding: 0 14px;
            gap: 8px;
        }

        #doc-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #666;
        }

        #doc-title {
            color: #e8e8e8;
            font-size: 14px;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
            min-width: 0;
            letter-spacing: 0.01em;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
            margin-left: auto;
        }

        .btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.15s, transform 0.1s;
            flex-shrink: 0;
            outline: none;
        }

        .btn:hover {
            opacity: 0.85
        }

        .btn:active {
            transform: scale(0.91)
        }

        .btn svg {
            width: 16px;
            height: 16px;
            stroke-width: 1.8;
            display: block;
            pointer-events: none;
        }

        .btn-print {
            background: #2d4a3e;
            color: #4ade80
        }

        .btn-download {
            background: #1e3a5f;
            color: #60a5fa
        }

        .btn-share {
            background: #4a2d1e;
            color: #fb923c
        }

        #pdf-viewer-close-btn {
            background: #dc2626;
            color: #fff;
            animation: pulse-once 0.6s ease-out 0.3s both;
        }

        @keyframes pulse-once {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.6);
            }

            50% {
                transform: scale(1.15);
                box-shadow: 0 0 0 8px rgba(220, 38, 38, 0);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0);
            }
        }

        @keyframes pulse-once {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.6);
            }

            50% {
                transform: scale(1.15);
                box-shadow: 0 0 0 8px rgba(220, 38, 38, 0);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0);
            }
        }

        #pdf-container {
            position: fixed;
            top: 52px;
            left: 0;
            right: 0;
            bottom: 0;
            overflow-y: auto;
            overflow-x: hidden;
            background: #1e1e1e;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 16px 12px;
            gap: 12px;
        }

        #pdf-container::-webkit-scrollbar {
            width: 4px;
        }

        #pdf-container::-webkit-scrollbar-track {
            background: transparent;
        }

        #pdf-container::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 0;
        }

        #pdf-container::-webkit-scrollbar-thumb:hover {
            background: #444;
        }

        .pdf-page {
            display: block;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.5);
            flex-shrink: 0;
            background: #fff;
            max-width: 100%;
            width: 100%;
            height: auto;
        }

        #loading {
            position: fixed;
            top: 52px;
            left: 0;
            right: 0;
            bottom: 0;
            background: #1e1e1e;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            z-index: 10;
        }

        #loading-spinner {
            width: 28px;
            height: 28px;
            border: 2px solid #2a2a2a;
            border-top-color: #60a5fa;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg)
            }
        }

        #loading-text {
            color: #555;
            font-size: 12px;
            letter-spacing: 0.04em;
        }

        #error-screen {
            display: none;
            position: fixed;
            top: 52px;
            left: 0;
            right: 0;
            bottom: 0;
            background: #1e1e1e;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #ef4444;
            font-size: 13px;
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"
                    stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
            </button>

            <button id="btn-download" class="btn btn-download">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
            </button>

            <button id="btn-share" class="btn btn-share">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"
                    stroke-linejoin="round">
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
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        var CMAP_URL = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/';
        var STANDARD_FONT = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/standard_fonts/';

        var PdfViewerState = {
            filename: @json($filename),
            blob: null,
            url: null,
            pdfDoc: null
        };

        var DocumentElements = {
            get container() {
                return document.getElementById('pdf-container')
            },
            get loading() {
                return document.getElementById('loading')
            },
            get loadingText() {
                return document.getElementById('loading-text')
            },
            get errorScreen() {
                return document.getElementById('error-screen')
            },
            get btnPrint() {
                return document.getElementById('btn-print')
            },
            get btnDownload() {
                return document.getElementById('btn-download')
            },
            get btnShare() {
                return document.getElementById('btn-share')
            },
            get btnClose() {
                return document.getElementById('pdf-viewer-close-btn')
            }
        };

        var PdfDecoder = {
            fromBase64: function(b64) {
                var binary = atob(b64);
                var bytes = new Uint8Array(binary.length);
                for (var i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
                return bytes.buffer;
            },
            toBlob: function(buf) {
                return new Blob([buf], {
                    type: 'application/pdf'
                });
            },
            toObjectUrl: function(blob) {
                return URL.createObjectURL(blob);
            }
        };

        var PdfRenderer = {
            scale: function() {
                var isMobile = window.innerWidth < 768;

                if (isMobile) {
                    var containerWidth = window.innerWidth - 24;
                    return (containerWidth / 595) * (window.devicePixelRatio || 1);
                }

                return 1.5;
            },

            renderPage: function(num) {
                return PdfViewerState.pdfDoc.getPage(num).then(function(page) {
                    var dpr = window.devicePixelRatio || 1;
                    var scale = PdfRenderer.scale();
                    var viewport = page.getViewport({
                        scale: scale
                    });

                    var canvas = document.createElement('canvas');
                    canvas.className = 'pdf-page';
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    canvas.style.width = (viewport.width / dpr) + 'px';
                    canvas.style.height = (viewport.height / dpr) + 'px';

                    DocumentElements.container.appendChild(canvas);

                    return page.render({
                        canvasContext: canvas.getContext('2d'),
                        viewport: viewport
                    }).promise;
                });
            },

            renderAll: function() {
                var total = PdfViewerState.pdfDoc.numPages;
                var promise = Promise.resolve();

                for (var i = 1; i <= total; i++) {
                    (function(num) {
                        promise = promise.then(function() {
                            DocumentElements.loadingText.textContent = 'Page ' + num + ' of ' + total;
                            return PdfRenderer.renderPage(num);
                        });
                    })(i);
                }

                return promise;
            }
        };

        var PdfActions = {
            download: function() {
                var a = document.createElement('a');
                a.href = PdfViewerState.url;
                a.download = PdfViewerState.filename;
                a.click();
            },

            print: function() {
                var iframe = document.createElement('iframe');
                iframe.style.cssText =
                    'position:fixed;top:-9999px;left:-9999px;width:1px;height:1px;visibility:hidden;';
                iframe.src = PdfViewerState.url;
                document.body.appendChild(iframe);
                iframe.onload = function() {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                    setTimeout(function() {
                        document.body.removeChild(iframe);
                    }, 60000);
                };
            },

            share: function() {
                var file = new File([PdfViewerState.blob], PdfViewerState.filename, {
                    type: 'application/pdf'
                });

                if (navigator.canShare && navigator.canShare({
                        files: [file]
                    })) {
                    navigator.share({
                            files: [file],
                            title: PdfViewerState.filename
                        })
                        .catch(function(e) {
                            if (e.name !== 'AbortError') console.error('Share failed', e);
                        });
                    return;
                }

                navigator.clipboard.writeText(location.href);
            },

            close: function() {
                if (window.self !== window.top) {
                    window.parent.$('.modal').modal('hide');
                } else {
                    window.close();
                }
            },
        };

        var PdfViewer = {
            initialize: function() {
                var base64 = @json($base64);
                var buffer = PdfDecoder.fromBase64(base64);

                PdfViewerState.blob = PdfDecoder.toBlob(buffer);
                PdfViewerState.url = PdfDecoder.toObjectUrl(PdfViewerState.blob);

                pdfjsLib.getDocument({
                    data: buffer,
                    cMapUrl: CMAP_URL,
                    cMapPacked: true,
                    standardFontDataUrl: STANDARD_FONT,
                    disableFontFace: true
                }).promise.then(function(pdfDoc) {
                    PdfViewerState.pdfDoc = pdfDoc;
                    return PdfRenderer.renderAll();
                }).then(function() {
                    DocumentElements.loading.style.display = 'none';
                    DocumentElements.btnPrint.addEventListener('click', PdfActions.print);
                    DocumentElements.btnDownload.addEventListener('click', PdfActions.download);
                    DocumentElements.btnShare.addEventListener('click', PdfActions.share);
                }).catch(function(err) {
                    console.error('PDF error', err);
                    DocumentElements.loading.style.display = 'none';
                    DocumentElements.errorScreen.style.display = 'flex';
                });
            }
        };

        var Application = {
            initialize: function() {
                PdfViewer.initialize();
                ApplicationUI.setupPrintButton();
            }
        };

        var ApplicationUI = {
            setupPrintButton: function() {
                var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

                if (isMobile) {
                    DocumentElements.btnPrint.style.display = 'none';
                }
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            Application.initialize();
            DocumentElements.btnClose.addEventListener('click', PdfActions.close);
        });
    </script>

</body>

</html>
