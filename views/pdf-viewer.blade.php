<!DOCTYPE html>
<html dir="{{ $dir }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $filename }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        body {
            height: 100vh;
            overflow: hidden;
            font-family: system-ui, sans-serif;
            background: #404040;
        }

        #toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 52px;
            z-index: 999;
            background: #212121;
            display: flex;
            align-items: center;
            padding: 0 16px;
            gap: 10px;
        }

        #doc-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #9e9e9e;
        }

        #doc-title {
            color: #eeeeee;
            font-size: 13px;
            font-weight: 400;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
            min-width: 0;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            margin-left: auto;
        }

        .btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: filter 0.15s, transform 0.1s;
            flex-shrink: 0;
            outline: none;
            line-height: 1;
        }

        .btn:hover {
            filter: brightness(1.15)
        }

        .btn:active {
            transform: scale(0.92)
        }

        .btn svg {
            width: 17px;
            height: 17px;
            stroke-width: 1.8;
            display: block;
            flex-shrink: 0;
            pointer-events: none;
        }

        .btn-print {
            background: #455a64;
            color: #fff
        }

        .btn-download {
            background: #37474f;
            color: #fff
        }

        .btn-share {
            background: #263238;
            color: #fff
        }

        #viewer {
            position: fixed;
            top: 52px;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: calc(100vh - 52px);
            border: none;
        }
    </style>
</head>

<body>

    <div id="toolbar">
        <div id="doc-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
            </svg>
        </div>

        <span id="doc-title">{{ $filename }}</span>

        <div class="actions">
            <button id="btn-print" class="btn btn-print">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
            </button>

            <button id="btn-download" class="btn btn-download">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
            </button>

            <button id="btn-share" class="btn btn-share">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="18" cy="5" r="3" />
                    <circle cx="6" cy="12" r="3" />
                    <circle cx="18" cy="19" r="3" />
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                </svg>
            </button>
        </div>
    </div>

    <iframe id="viewer"></iframe>

    <script>
        var PdfViewerState = {
            filename: @json($filename),
            blob: null,
            url: null
        };

        var DocumentElements = {
            get viewerElement() {
                return document.getElementById('viewer');
            },
            get btnDownload() {
                return document.getElementById('btn-download');
            },
            get btnPrint() {
                return document.getElementById('btn-print');
            },
            get btnShare() {
                return document.getElementById('btn-share');
            }
        };

        var PdfDecoder = {
            fromBase64: function(base64String) {
                var binary = atob(base64String);
                var bytes = new Uint8Array(binary.length);

                for (var i = 0; i < binary.length; i++) {
                    bytes[i] = binary.charCodeAt(i);
                }

                return bytes.buffer;
            },
            toBlob: function(arrayBuffer) {
                return new Blob([arrayBuffer], {
                    type: 'application/pdf'
                });
            },
            toObjectUrl: function(blob) {
                return URL.createObjectURL(blob);
            }
        };

        var PdfActions = {
            download: function() {
                var anchor = document.createElement('a');
                anchor.href = PdfViewerState.url;
                anchor.download = PdfViewerState.filename;
                anchor.click();
            },
            print: function() {
                var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

                if (isMobile) {
                    PdfActions.share();
                    return;
                }

                var iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = PdfViewerState.url;

                document.body.appendChild(iframe);

                iframe.onload = function() {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                };
            },
            share: async function() {
                var file = new File(
                    [PdfViewerState.blob],
                    PdfViewerState.filename, {
                        type: 'application/pdf'
                    }
                );

                if (navigator.canShare && navigator.canShare({
                        files: [file]
                    })) {
                    try {
                        await navigator.share({
                            files: [file],
                            title: PdfViewerState.filename
                        });
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            console.error('Share failed');
                        }
                    }

                    return;
                }

                await navigator.clipboard.writeText(location.href);
            }
        };

        var PdfViewer = {
            initialize: function() {
                var base64 = @json($base64);
                var buffer = PdfDecoder.fromBase64(base64);

                PdfViewerState.blob = PdfDecoder.toBlob(buffer);
                PdfViewerState.url = PdfDecoder.toObjectUrl(PdfViewerState.blob);

                DocumentElements.viewerElement.src = PdfViewerState.url + '#toolbar=0&navpanes=0';

                DocumentElements.btnDownload.addEventListener('click', PdfActions.download);
                DocumentElements.btnPrint.addEventListener('click', PdfActions.print);
                DocumentElements.btnShare.addEventListener('click', PdfActions.share);
            }
        };

        var Application = {
            initialize: function() {
                PdfViewer.initialize();
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            Application.initialize();
        });
    </script>

</body>

</html>
