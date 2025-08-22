<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Templates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .input-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.1) !important;
        }

        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-gray-50 font-sans text-sm">

    <div class="min-h-screen p-6">
        <div class="max-w-6xl mx-auto">

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Invoice Templates</h1>
                    <p class="text-gray-600 text-sm">Create and manage your PDF templates</p>
                </div>
                <button onclick="TemplateModal.open()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Template
                </button>
            </div>

            <div id="alertContainer" class="mb-4 hidden">
                <div id="alertMessage" class="px-4 py-3 rounded-lg border"></div>
            </div>

            <div id="templatesContainer" class="bg-white rounded-lg border border-gray-200 shadow-sm hidden">
                <div class="border-b border-gray-200">
                    <div class="px-4 py-3 flex justify-between items-center">
                        <h2 class="font-semibold text-gray-900">Templates</h2>
                        <div class="text-xs text-gray-500">
                            <span id="templateCount">0</span> found
                        </div>
                    </div>

                    <div class="px-4 pb-3">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" id="searchInput"
                                placeholder="Search by slug, language, paper, or status..."
                                class="w-full pl-10 pr-8 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                oninput="TemplateSearch.filter()" />
                            <button id="clearSearch" onclick="TemplateSearch.clear()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 hidden">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                    Slug</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                    Lang</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                    Paper</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody id="templatesTableBody" class="divide-y divide-gray-100">
                        </tbody>
                    </table>
                </div>

                <div id="noSearchResults" class="hidden p-6 text-center border-t border-gray-100">
                    <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <p class="text-sm text-gray-600 mb-2">No templates found</p>
                    <button onclick="TemplateSearch.clear()" class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                        Clear search
                    </button>
                </div>
            </div>

            <div id="emptyState" class="hidden bg-white border border-gray-200 shadow-sm rounded-lg">
                <div class="text-center py-12">
                    <div class="max-w-md mx-auto">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No templates found</h3>
                        <p class="text-gray-600 mb-4">Create your first template to get started</p>
                        <button onclick="TemplateModal.open()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm">
                            Create Template
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="templateModal" class="fixed inset-0 z-40 bg-black bg-opacity-50 hidden">
        <div class="h-screen flex flex-col">

            <div class="bg-white px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 id="modalTitle" class="text-xl font-bold text-gray-900">New Template</h2>
                        <p class="text-gray-600 text-sm">Configure your template settings</p>
                    </div>
                    <button onclick="TemplateModal.close()"
                        class="text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-hidden bg-gray-50">
                <div class="h-full overflow-y-auto">
                    <div class="max-w-4xl mx-auto p-6">
                        <form id="templateForm" class="space-y-6">
                            <input type="hidden" id="templateId" name="id">

                            <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Settings</h3>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Template Slug
                                            *</label>
                                        <input type="text" id="page" name="page"
                                            placeholder="invoice-template-en"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                            oninput="FormValidation.clearError('page')" required />
                                        <div id="pageError" class="text-red-500 text-xs mt-1 font-medium hidden">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Language Code
                                            *</label>
                                        <input type="text" id="lang" name="lang" placeholder="en"
                                            maxlength="5"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white"
                                            oninput="FormValidation.clearError('lang')" required />
                                        <div id="langError" class="text-red-500 text-xs mt-1 font-medium hidden">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" id="isActive" name="is_active"
                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                            checked />
                                        <span class="ml-2 text-sm text-gray-700">Active Template</span>
                                    </label>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Page Configuration</h3>

                                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Paper Size</label>
                                        <select id="paperSize" name="paper_size" onchange="PaperConfig.updateDimensions()"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="A4">A4</option>
                                            <option value="A5">A5</option>
                                            <option value="A3">A3</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Orientation</label>
                                        <select id="orientation" name="orientation"
                                            onchange="PaperConfig.updateDimensions()"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="portrait">Portrait</option>
                                            <option value="landscape">Landscape</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Width (mm)</label>
                                        <input type="number" id="width" name="width" step="0.1"
                                            value="210"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Height (mm)</label>
                                        <input type="number" id="height" name="height" step="0.1"
                                            value="297"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white" />
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Margins & Spacing (mm)</h4>
                                    <div class="grid grid-cols-3 lg:grid-cols-6 gap-3">
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Top</label>
                                            <input type="number" id="marginTop" name="margin_top" step="0.1"
                                                value="20"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-xs bg-white" />
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Bottom</label>
                                            <input type="number" id="marginBottom" name="margin_bottom"
                                                step="0.1" value="20"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-xs bg-white" />
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Left</label>
                                            <input type="number" id="marginLeft" name="margin_left" step="0.1"
                                                value="20"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-xs bg-white" />
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Right</label>
                                            <input type="number" id="marginRight" name="margin_right"
                                                step="0.1" value="20"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-xs bg-white" />
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Header</label>
                                            <input type="number" id="headerSpace" name="header_space"
                                                step="0.1" value="10"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-xs bg-white" />
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Footer</label>
                                            <input type="number" id="footerSpace" name="footer_space"
                                                step="0.1" value="10"
                                                class="w-full px-2 py-2 border border-gray-300 rounded text-xs bg-white" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Content Sections</h3>

                                <div class="space-y-4">
                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <label class="block text-sm font-medium text-gray-700">Header Content
                                                *</label>
                                            <button type="button" onclick="PreviewModal.open('header')"
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                                Preview
                                            </button>
                                        </div>
                                        <textarea id="header" name="header" rows="4" placeholder="Enter header HTML content..."
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-xs bg-white"
                                            oninput="FormValidation.clearError('header')" required></textarea>
                                        <div id="headerError" class="text-red-500 text-xs mt-1 font-medium hidden">
                                        </div>
                                    </div>

                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <label class="block text-sm font-medium text-gray-700">Main Content
                                                *</label>
                                            <button type="button" onclick="PreviewModal.open('content')"
                                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                                Preview
                                            </button>
                                        </div>
                                        <textarea id="content" name="content" rows="6" placeholder="Enter main content HTML (optional)..."
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-xs bg-white"
                                            oninput="FormValidation.clearError('content')" required></textarea>
                                        <div id="contentError" class="text-red-500 text-xs mt-1 font-medium hidden">
                                        </div>
                                    </div>

                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <label class="block text-sm font-medium text-gray-700">Footer
                                                Content</label>
                                            <button type="button" onclick="PreviewModal.open('footer')"
                                                class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                                Preview
                                            </button>
                                        </div>
                                        <textarea id="footer" name="footer" rows="3" placeholder="Enter footer HTML content (optional)..."
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-xs bg-white"
                                            oninput="FormValidation.clearError('footer')"></textarea>
                                        <div id="footerError" class="text-red-500 text-xs mt-1 font-medium hidden">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="bg-white px-6 py-4 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-xs text-gray-500">
                        Press Ctrl+S to save
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="TemplateModal.close()"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                            Cancel
                        </button>
                        <button type="button" onclick="TemplateAPI.save()" id="submitBtn"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors disabled:opacity-50 shadow-sm flex items-center gap-2">
                            <svg id="submitSpinner" class="hidden w-4 h-4 spinner" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                                    fill="none" opacity="0.25"></circle>
                                <path fill="currentColor" opacity="0.75"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span id="submitText">Save Template</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="previewModal" class="fixed inset-0 z-50 bg-black bg-opacity-60 hidden">
        <div class="h-screen flex flex-col">

            <div class="bg-white px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 id="previewModalTitle" class="text-xl font-bold text-gray-900">Preview</h2>
                        <p class="text-gray-600 text-sm">HTML preview of your content</p>
                    </div>
                    <button onclick="PreviewModal.close()"
                        class="text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 bg-gray-100 p-6">
                <div class="h-full bg-white rounded-lg overflow-hidden">
                    <iframe id="previewFrame" class="w-full h-full border-0" sandbox="allow-same-origin"></iframe>
                </div>
            </div>

            <div class="bg-white px-6 py-4 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <span id="previewType" class="text-gray-600 text-sm">Previewing: Header</span>
                    <button onclick="PreviewModal.close()"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors shadow-sm">
                        Close Preview
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const AppState = {
            templates: [],
            filteredTemplates: [],
            editingTemplate: null,
        };

        const Elements = {
            get searchInput() { return document.getElementById('searchInput'); },
            get clearSearchBtn() { return document.getElementById('clearSearch'); },
            get templateCount() { return document.getElementById('templateCount'); },
            get templatesContainer() { return document.getElementById('templatesContainer'); },
            get emptyState() { return document.getElementById('emptyState'); },
            get noSearchResults() { return document.getElementById('noSearchResults'); },
            get tableBody() { return document.getElementById('templatesTableBody'); },
            get templateModal() { return document.getElementById('templateModal'); },
            get previewModal() { return document.getElementById('previewModal'); },
            get templateForm() { return document.getElementById('templateForm'); },
            get submitBtn() { return document.getElementById('submitBtn'); },
            get submitSpinner() { return document.getElementById('submitSpinner'); },
            get submitText() { return document.getElementById('submitText'); },
            get modalTitle() { return document.getElementById('modalTitle'); },
            get previewFrame() { return document.getElementById('previewFrame'); },
            get previewModalTitle() { return document.getElementById('previewModalTitle'); },
            get previewType() { return document.getElementById('previewType'); }
        };

        const HttpClient = {
            init() {
                const csrfToken = document.querySelector('meta[name="csrf-token"]');

                if (csrfToken) {
                    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
                }
            },

            async get(url, config = {}) {
                try {
                    return await axios.get(url, config);
                } catch (error) {
                    throw this.handleError(error);
                }
            },

            async post(url, data, config = {}) {
                try {
                    return await axios.post(url, data, config);
                } catch (error) {
                    throw this.handleError(error);
                }
            },

            async delete(url, config = {}) {
                try {
                    return await axios.delete(url, config);
                } catch (error) {
                    throw this.handleError(error);
                }
            },

            handleError(error) {
                return error;
            }
        };

        const Utils = {
            capitalize(str) {
                return str.charAt(0).toUpperCase() + str.slice(1);
            },

            formatPaperSize(template) {
                const size = template.paper_size;
                const orientation = template.orientation;
                return `${size} ${this.capitalize(orientation)}`;
            },

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            },

            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
        };

        const ErrorHandler = {
            getErrorMessage(error) {
                if (error.response) {
                    if (error.response.data?.message) {
                        return error.response.data.message;
                    }

                    if (error.response.data?.error) {
                        return error.response.data.error;
                    }

                    return `Server error: ${error.response.status} ${error.response.statusText}`;
                }
                
                if (error.request) {
                    return 'Network error: Unable to connect to server';
                }
                
                return error.message || 'An unexpected error occurred';
            },

            showMainAlert(message, type = 'info') {
                const container = document.getElementById('alertContainer');
                const alertMessage = document.getElementById('alertMessage');
                
                if (! container || ! alertMessage) {
                    return;
                }

                container.classList.remove('hidden');
                alertMessage.textContent = message;

                alertMessage.className = 'px-4 py-3 rounded-lg border';

                const typeClasses = {
                    success: 'bg-green-100 border-green-200 text-green-700',
                    error: 'bg-red-100 border-red-200 text-red-700',
                    info: 'bg-blue-100 border-blue-200 text-blue-700'
                };

                alertMessage.classList.add(...(typeClasses[type] || typeClasses.info).split(' '));

                setTimeout(() => container.classList.add('hidden'), 6000);

                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            showToast(message, type = 'info') {
                const existingToast = document.getElementById('modalToast');

                if (existingToast) {
                    existingToast.remove();
                }

                const toast = document.createElement('div');
                toast.id = 'modalToast';
                toast.className = 'fixed top-4 right-4 z-[70] px-4 py-3 rounded-lg border transform transition-all duration-300 translate-x-full max-w-sm shadow-sm';

                const typeClasses = {
                    success: 'bg-green-50 border-green-200 text-green-800',
                    error: 'bg-red-50 border-red-200 text-red-800',
                    info: 'bg-blue-50 border-blue-200 text-blue-800'
                };

                toast.classList.add(...(typeClasses[type] || typeClasses.info).split(' '));

                toast.innerHTML = `
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <p class="font-medium text-xs">${Utils.escapeHtml(message)}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                `;

                document.body.appendChild(toast);

                setTimeout(() => toast.classList.remove('translate-x-full'), 100);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.classList.add('translate-x-full');
                        setTimeout(() => toast.remove(), 300);
                    }
                }, 4000);
            }
        };

        const FormValidation = {
            clearError(fieldName) {
                const errorElement = document.getElementById(`${fieldName}Error`);
                const inputElement = document.getElementById(fieldName);
                
                if (errorElement) {
                    errorElement.classList.add('hidden');
                }
                
                if (inputElement) {
                    inputElement.classList.remove('input-error');
                }
            },

            showError(fieldName, message) {
                const errorElement = document.getElementById(`${fieldName}Error`);
                const inputElement = document.getElementById(fieldName);
                
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.classList.remove('hidden');
                }
                
                if (inputElement) {
                    inputElement.classList.add('input-error');
                }
            },

            clearAllErrors() {
                document.querySelectorAll('[id$="Error"]').forEach(el => {
                    el.classList.add('hidden');

                    const input = el.previousElementSibling;
                    
                    if (input?.classList) {
                        input.classList.remove('input-error');
                    }
                });
            },

            validateRequired(fields) {
                let hasErrors = false;
                
                fields.forEach(fieldName => {
                    const element = document.getElementById(fieldName);

                    if (! element || element.value.trim() === '') {
                        this.showError(fieldName, 'This field is required');
                        hasErrors = true;
                    }
                });
                
                return ! hasErrors;
            },

            handleServerErrors(errors) {
                Object.entries(errors).forEach(([field, messages]) => {
                    if (Array.isArray(messages) && messages.length > 0) {
                        this.showError(field, messages[0]);
                    }
                });
            }
        };

        const TemplateSearch = {
            filter: Utils.debounce(function() {
                const searchTerm = Elements.searchInput.value.toLowerCase().trim();
                
                if (searchTerm === '') {
                    AppState.filteredTemplates = [...AppState.templates];
                    Elements.clearSearchBtn.classList.add('hidden');
                } else {
                    AppState.filteredTemplates = AppState.templates.filter(template => {
                        const searchFields = [
                            template.page,
                            template.lang,
                            template.paper_size,
                            template.orientation,
                            template.is_active ? 'active' : 'inactive'
                        ];
                        
                        return searchFields.some(field => 
                            field.toLowerCase().includes(searchTerm)
                        );
                    });

                    Elements.clearSearchBtn.classList.remove('hidden');
                }
                
                TemplateTable.render();
                this.updateCount();
            }, 300),

            clear() {
                Elements.searchInput.value = '';
                Elements.clearSearchBtn.classList.add('hidden');
                AppState.filteredTemplates = [...AppState.templates];
                TemplateTable.render();
                this.updateCount();
            },

            updateCount() {
                if (Elements.templateCount) {
                    Elements.templateCount.textContent = AppState.filteredTemplates.length;
                }
            }
        };

        const TemplateTable = {
            render() {
                const hasSearchTerm = Elements.searchInput.value.trim() !== '';
                
                if (AppState.templates.length === 0) {
                    this.showEmptyState();
                    return;
                }

                this.showTemplatesContainer();

                if (AppState.filteredTemplates.length === 0 && hasSearchTerm) {
                    this.showNoSearchResults();
                    return;
                }

                this.hideNoSearchResults();
                this.renderTableRows();
            },

            showEmptyState() {
                Elements.tableBody.innerHTML = '';
                Elements.templatesContainer.classList.add('hidden');
                Elements.emptyState.classList.remove('hidden');
                Elements.noSearchResults.classList.add('hidden');
            },

            showTemplatesContainer() {
                Elements.emptyState.classList.add('hidden');
                Elements.templatesContainer.classList.remove('hidden');
                Elements.templatesContainer.className = 'bg-white rounded-lg border border-gray-200 shadow-sm';
            },

            showNoSearchResults() {
                Elements.tableBody.innerHTML = '';
                Elements.noSearchResults.classList.remove('hidden');
            },

            hideNoSearchResults() {
                Elements.noSearchResults.classList.add('hidden');
            },

            renderTableRows() {
                Elements.tableBody.innerHTML = AppState.filteredTemplates
                    .map(this.createTableRow)
                    .join('');
            },

            createTableRow(template) {
                const slug = Utils.escapeHtml(template.page);
                const language = Utils.escapeHtml((template.lang).toUpperCase());
                const paperFormat = Utils.escapeHtml(Utils.formatPaperSize(template));
                const statusClass = template.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                const statusText = template.is_active ? 'Active' : 'Inactive';

                return `<tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900 text-sm">${slug}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                ${language}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700 text-sm">
                            ${paperFormat}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium ${statusClass}">
                                ${statusText}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button 
                                    onclick="TemplateModal.edit(${template.id})"
                                    class="p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors"
                                    title="Edit template"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button 
                                    onclick="TemplateAPI.delete(${template.id})"
                                    class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Delete template"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>`;
            }
        };

        const PaperConfig = {
            dimensions: {
                'A4': { width: 210, height: 297 },
                'A5': { width: 148, height: 210 },
                'A3': { width: 297, height: 420 }
            },

            updateDimensions() {
                const paperSize = document.getElementById('paperSize')?.value;
                const orientation = document.getElementById('orientation')?.value;
                
                const dimensions = this.dimensions[paperSize];
                
                const widthElement = document.getElementById('width');
                const heightElement = document.getElementById('height');
                
                if (widthElement && heightElement) {
                    if (orientation === 'landscape') {
                        widthElement.value = dimensions.height;
                        heightElement.value = dimensions.width;
                    } else {
                        widthElement.value = dimensions.width;
                        heightElement.value = dimensions.height;
                    }
                }
            }
        };

        const TemplateModal = {
            open() {
                AppState.editingTemplate = null;
                Elements.modalTitle.textContent = 'New Template';
                Elements.submitText.textContent = 'Save Template';
                Elements.templateModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            },

            close() {
                Elements.templateModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            },

            edit(id) {
                const template = AppState.templates.find(t => t.id === id);

                if (! template) {
                    ErrorHandler.showMainAlert('Template not found', 'error');
                    return;
                }

                AppState.editingTemplate = template;
                Elements.modalTitle.textContent = 'Edit Template';
                Elements.submitText.textContent = 'Update Template';
                
                this.populateForm(template);
                Elements.templateModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            },

            resetForm() {
                Elements.templateForm.reset();
                document.getElementById('templateId').value = '';
                FormValidation.clearAllErrors();
                PaperConfig.updateDimensions();
            },

            populateForm(template) {
                const fieldMappings = {
                    'templateId': template.id,
                    'page': template.page,
                    'lang': template.lang,
                    'isActive': template.is_active,
                    'paperSize': template.paper_size,
                    'orientation': template.orientation,
                    'width': template.width,
                    'height': template.height,
                    'marginTop': template.margin_top,
                    'marginBottom': template.margin_bottom,
                    'marginLeft': template.margin_left,
                    'marginRight': template.margin_right,
                    'headerSpace': template.header_space,
                    'footerSpace': template.footer_space,
                    'header': template.header,
                    'content': template.content,
                    'footer': template.footer
                };

                Object.entries(fieldMappings).forEach(([fieldId, value]) => {
                    const element = document.getElementById(fieldId);
                    
                    if (! element) {
                        return;
                    }

                    if (element.type === 'checkbox') {
                        element.checked = Boolean(value);
                    } else {
                        element.value = value;
                    }
                });
            }
        };

        const PreviewModal = {
            open(type) {
                const content = document.getElementById(type)?.value;
                
                if (content.trim() === '') {
                    ErrorHandler.showToast(`Please enter ${type} content first`, 'error');
                    return;
                }

                const previewHtml = this.generatePreviewHtml(type, content);
                
                Elements.previewModalTitle.textContent = `${Utils.capitalize(type)} Preview`;
                Elements.previewType.textContent = `Previewing: ${Utils.capitalize(type)}`;
                Elements.previewFrame.srcdoc = previewHtml;
                Elements.previewModal.classList.remove('hidden');
            },

            close() {
                Elements.previewModal.classList.add('hidden');
            },

            generatePreviewHtml(type, content) {
                return `<!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>${Utils.capitalize(type)} Preview</title>
                        <style>
                            body {
                                font-family: system-ui, sans-serif;
                                margin: 20px;
                                line-height: 1.5;
                                background: white;
                                color: #374151;
                                font-size: 14px;
                            }

                            .preview-container {
                                max-width: 100%;
                                margin: 0 auto;
                                padding: 20px;
                            }

                            img {
                                max-width: 100%;
                                height: auto;
                            }

                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin: 15px 0;
                            }

                            th,
                            td {
                                border: 1px solid #d1d5db;
                                padding: 8px;
                                text-align: left;
                            }

                            th {
                                background-color: #f9fafb;
                                font-weight: 600;
                            }

                            h1,
                            h2,
                            h3,
                            h4,
                            h5,
                            h6 {
                                color: #111827;
                                margin: 15px 0 10px 0;
                            }

                            p {
                                margin-bottom: 12px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="preview-container">
                            ${content}
                        </div>
                    </body>
                </html>`;
            }
        };

        const TemplateAPI = {
            async load() {
                try {
                    const response = await HttpClient.get('/invoice-templates/get-data');
                    AppState.templates = response.data || [];
                    AppState.filteredTemplates = [...AppState.templates];
                    TemplateTable.render();
                    TemplateSearch.updateCount();
                } catch (error) {
                    ErrorHandler.showMainAlert(
                        'Error loading templates: ' + ErrorHandler.getErrorMessage(error), 
                        'error'
                    );
                }
            },

            async save() {
                FormValidation.clearAllErrors();

                const requiredFields = ['page', 'lang'];

                if (! FormValidation.validateRequired(requiredFields)) {
                    ErrorHandler.showToast('Please fill in all required fields', 'error');
                    return;
                }

                const submitBtn = Elements.submitBtn;
                const submitSpinner = Elements.submitSpinner;
                const submitText = Elements.submitText;

                submitBtn.disabled = true;
                submitSpinner.classList.remove('hidden');
                submitText.textContent = 'Saving...';

                try {
                    const formData = new FormData(Elements.templateForm);
                    let response;

                    if (AppState.editingTemplate) {
                        formData.append('_method', 'PUT');
                        response = await HttpClient.post(
                            `/invoice-templates/update/${AppState.editingTemplate.id}`, 
                            formData
                        );
                    } else {
                        response = await HttpClient.post('/invoice-templates/store', formData);
                    }

                    const successMessage = AppState.editingTemplate ? 
                        'Template updated successfully!' : 
                        'Template created successfully!';
                    
                    ErrorHandler.showToast(successMessage, 'success');
                    TemplateModal.close();
                    await this.load();

                } catch (error) {
                    if (error.response?.status === 422) {
                        const errors = error.response.data?.errors || {};
                        FormValidation.handleServerErrors(errors);
                        ErrorHandler.showToast('Please fix the validation errors', 'error');
                    } else {
                        ErrorHandler.showToast(
                            'Error saving template: ' + ErrorHandler.getErrorMessage(error), 
                            'error'
                        );
                    }
                } finally {
                    submitBtn.disabled = false;
                    submitSpinner.classList.add('hidden');
                    submitText.textContent = AppState.editingTemplate ? 'Update Template' : 'Save Template';
                }
            },

            async delete(id) {
                const result = await Swal.fire({
                    title: 'Delete Template ?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                });

                if (! result.isConfirmed) {
                    return;
                }

                try {
                    await HttpClient.delete(`/invoice-templates/delete/${id}`);
                    
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Template has been deleted.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    
                    await this.load();
                } catch (error) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete template: ' + ErrorHandler.getErrorMessage(error),
                        icon: 'error'
                    });
                }
            }
        };

        const KeyboardShortcuts = {
            init() {
                document.addEventListener('keydown', this.handleKeyDown.bind(this));
            },

            handleKeyDown(event) {
                if (event.key === 'Escape') {
                    if (! Elements.previewModal.classList.contains('hidden')) {
                        PreviewModal.close();
                    } else if (! Elements.templateModal.classList.contains('hidden')) {
                        TemplateModal.close();
                    }
                    
                    return;
                }

                if (event.ctrlKey && event.key === 's' && ! Elements.templateModal.classList.contains('hidden')) {
                    event.preventDefault();
                    TemplateAPI.save();
                    return;
                }
            }
        };

        const App = {
            async init() {
                try {
                    HttpClient.init();
                    KeyboardShortcuts.init();
                    await TemplateAPI.load();
                } catch (error) {
                    ErrorHandler.showMainAlert(
                        'Failed to initialize application: ' + ErrorHandler.getErrorMessage(error), 
                        'error'
                    );
                }
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            App.init();
        });

        window.openModal = () => TemplateModal.open();
        window.closeModal = () => TemplateModal.close();
        window.openPreviewModal = (type) => PreviewModal.open(type);
        window.closePreviewModal = () => PreviewModal.close();
        window.editTemplate = (id) => TemplateModal.edit(id);
        window.deleteTemplate = (id) => TemplateAPI.delete(id);
        window.saveTemplate = () => TemplateAPI.save();
        window.filterTemplates = () => TemplateSearch.filter();
        window.clearSearch = () => TemplateSearch.clear();
        window.updatePaperDimensions = () => PaperConfig.updateDimensions();
        window.clearError = (fieldName) => FormValidation.clearError(fieldName);
    </script>
</body>

</html>