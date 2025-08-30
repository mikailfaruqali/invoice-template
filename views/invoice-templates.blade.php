<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Templates</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .input-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.1) !important;
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
                <button onclick="TemplateModal.open()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm">
                    New Template
                </button>
            </div>

            <div id="alertContainer" class="mb-4 hidden">
                <div id="alertMessage" class="px-4 py-3 rounded-lg border">
                    <span class="flex-1"></span>
                </div>
            </div>

            <div id="templatesContainer" class="bg-white rounded-lg border border-gray-200 shadow-sm hidden">
                <div class="border-b border-gray-200">
                    <div class="px-4 py-3 flex justify-between items-center">
                        <h2 class="font-semibold text-gray-900">Templates</h2>
                        <div class="text-xs text-gray-500">
                            <span id="templateCount">0</span> total
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Slugs</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Lang</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Paper</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="templatesTableBody" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
            </div>

            <div id="emptyState" class="hidden bg-white border border-gray-200 shadow-sm rounded-lg">
                <div class="text-center py-12">
                    <div class="max-w-md mx-auto">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No templates found</h3>
                        <p class="text-gray-600 mb-4">Create your first template to get started</p>
                        <button onclick="TemplateModal.open()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium shadow-sm">
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
                    <button onclick="TemplateModal.close()" class="text-gray-600 hover:text-gray-800 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        Close
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-hidden bg-gray-50">
                <div class="h-full overflow-y-auto">
                    <div class="max-w-4xl mx-auto p-6">
                        <form id="templateForm" class="space-y-6">
                            <input type="hidden" id="templateId" name="id">
                            <input type="hidden" id="page" name="page">

                            <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Settings</h3>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div x-data="MultiSlugTagsInputComponent()" x-init="initialize(INITIAL_PAGE_SLUGS)" id="slugTagsBox">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Page Slugs *</label>

                                        <div class="border border-gray-300 rounded-lg bg-white p-2 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500">
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="(slugValue, slugIndex) in selectedValues" :key="slugValue">
                                                    <span class="inline-flex items-center bg-blue-50 text-blue-800 border border-blue-200 rounded-full px-2 py-1 text-xs">
                                                        <span x-text="slugValue"></span>
                                                    <button type="button" class="ml-1 text-blue-600 hover:text-blue-800" @click="removeByIndex(slugIndex)">Remove</button>
                                                    </span>
                                                </template>

                                                <input x-model="inputValue" @keydown.enter.prevent="addFromInput()" @keydown.,.prevent="addFromInput()" @keydown.tab="addIfFilled()" placeholder="type slug and Enter" class="min-w-[10ch] flex-1 px-2 py-1 text-sm outline-none">
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <div class="text-xs text-gray-500 mb-1">Available</div>
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="option in availableOptions" :key="option">
                                                    <button type="button" class="px-2 py-1 rounded border text-xs" :class="selectedValues.includes(option) ? 'bg-gray-200 border-gray-300 text-gray-800' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'" @click="toggleValue(option)" x-text="option">
                                                    </button>
                                                </template>
                                            </div>
                                        </div>

                                        <input id="page_required_guard" type="text" class="hidden" required x-bind:value="selectedValues.length ? 'ok' : ''">
                                        <div id="pageError" class="text-red-500 text-xs mt-1 font-medium hidden"></div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Language Code *</label>
                                        <input type="text" id="lang" name="lang" placeholder="en" maxlength="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white" oninput="FormValidation.showNone('lang')"
                                        required>
                                        <div id="langError" class="text-red-500 text-xs mt-1 font-medium hidden"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Page Configuration</h3>

                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Paper Size</label>
                                        <select id="paperSize" name="paper_size" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="A4">A4</option>
                                            <option value="A5">A5</option>
                                            <option value="A3">A3</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Orientation</label>
                                        <select id="orientation" name="orientation" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="portrait">Portrait</option>
                                            <option value="landscape">Landscape</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Margins &amp; Spacing (mm)</h4>
                                    <div class="grid grid-cols-3 lg:grid-cols-6 gap-3">
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Top</label>
                                            <input type="number" id="marginTop" name="margin_top" step="0.1" value="20" class="w-full px-2 py-2 border border-gray-300 rounded text-xs bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Bottom</label>
                                            <input type="number" id="marginBottom" name="margin_bottom" step="0.1" value="20" class="w-full px-2 py-2 border border-gray-300 rounded text-xs bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Left</label>
                                            <input type="number" id="marginLeft" name="margin_left" step="0.1" value="20" class="w-full px-2 py-2 border border-gray-300 rounded text-xs bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Right</label>
                                            <input type="number" id="marginRight" name="margin_right" step="0.1" value="20" class="w-full px-2 py-2 border border-gray-300 rounded text-xs bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Header</label>
                                            <input type="number" id="headerSpace" name="header_space" step="0.1" value="10" class="w-full px-2 py-2 border border-gray-300 rounded text-xs bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-600 mb-1">Footer</label>
                                            <input type="number" id="footerSpace" name="footer_space" step="0.1" value="10" class="w-full px-2 py-2 border border-gray-300 rounded text-xs bg-white">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" id="disabled-smart-shrinking" name="disabled_smart_shrinking" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-gray-700">Disable Smart Shrinking</span>
                                    </label>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Content Sections</h3>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Header Content *</label>
                                        <textarea id="header" name="header" rows="4" placeholder="Enter header content HTML..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-xs bg-white" oninput="FormValidation.showNone('header')"
                                        required></textarea>
                                        <div id="headerError" class="text-red-500 text-xs mt-1 font-medium hidden"></div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Main Content *</label>
                                        <textarea id="content" name="content" rows="6" placeholder="Enter main content HTML..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-xs bg-white" oninput="FormValidation.showNone('content')"
                                        required></textarea>
                                        <div id="contentError" class="text-red-500 text-xs mt-1 font-medium hidden"></div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Footer Content</label>
                                        <textarea id="footer" name="footer" rows="3" placeholder="Enter footer content HTML..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-xs bg-white" oninput="FormValidation.showNone('footer')"></textarea>
                                        <div id="footerError" class="text-red-500 text-xs mt-1 font-medium hidden"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Security Verification</h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                                    <input type="password" id="password" name="password" placeholder="Enter password to save template changes" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-xs bg-white" oninput="FormValidation.showNone('password')"
                                    required>
                                    <div id="passwordError" class="text-red-500 text-xs mt-1 font-medium hidden"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="bg-white px-6 py-4 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-xs text-gray-500">
                        Press Ctrl + S to save
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="TemplateModal.close()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                            Cancel
                        </button>
                        <button type="button" onclick="TemplateAPI.save()" id="submitBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors disabled:opacity-50 shadow-sm">
                            <span id="submitText">Save Template</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        var INITIAL_PAGE_SLUGS = @json(config('snawbar-invoice-template.page-slugs', []));
    
        var TemplateApplicationState = {
            templates: [],
            filteredTemplates: [],
            editingTemplate: null
        };
    
        var DocumentElements = {
            get templateCountElement() {
                return document.getElementById('templateCount');
            },
            get templatesContainerElement() {
                return document.getElementById('templatesContainer');
            },
            get emptyStateElement() {
                return document.getElementById('emptyState');
            },
            get tableBodyElement() {
                return document.getElementById('templatesTableBody');
            },
            get templateModalElement() {
                return document.getElementById('templateModal');
            },
            get templateFormElement() {
                return document.getElementById('templateForm');
            },
            get submitButtonElement() {
                return document.getElementById('submitBtn');
            },
            get submitTextElement() {
                return document.getElementById('submitText');
            },
            get modalTitleElement() {
                return document.getElementById('modalTitle');
            }
        };
    
        var HttpClientService = {
            initialize: function() {
                var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    
                if (csrfTokenMeta) {
                    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfTokenMeta.getAttribute('content');
                }
            },
            get: async function(url, config) {
                try {
                    return await axios.get(url, config || {});
                }
                
                catch (error) {
                    throw error;
                }
            },
            post: async function(url, data, config) {
                try {
                    return await axios.post(url, data, config || {});
                }

                catch (error) {
                    throw error;
                }
            },
            delete: async function(url, config) {
                try {
                    return await axios.delete(url, config || {});
                }

                catch (error) {
                    throw error;
                }
            }
        };
    
        var UtilityFunctions = {
            capitalize: function(inputString) {
                if (!inputString || typeof inputString !== 'string') {
                    return '';
                }
    
                return inputString.charAt(0).toUpperCase() + inputString.slice(1);
            },
            formatPaperSize: function(templateItem) {
                var sizeValue = templateItem.paper_size;
                var orientationValue = templateItem.orientation;
    
                return String(sizeValue) + ' ' + this.capitalize(orientationValue);
            },
            escapeHtml: function(inputText) {
                var temporaryDiv = document.createElement('div');
                temporaryDiv.textContent = inputText || '';
    
                return temporaryDiv.innerHTML;
            },
            parseJsonString: function(text) {
                try {
                    return JSON.parse(text);
                }

                catch (error) {
                    return null;
                }
            },
            splitCommaSeparated: function(text) {
                return text.split(',').map(function(value) { return value.trim(); }).filter(function(value) { return value.length > 0; });
            },
            normalizeParsedToArrayOfStrings: function(parsed) {
                if (Array.isArray(parsed)) {
                    return parsed.map(function(value) { return String(value); });
                }
    
                if (typeof parsed === 'string') {
                    return [parsed];
                }
    
                return null;
            },
            toSlugArray: function(maybeJson) {
                if (Array.isArray(maybeJson)) {
                    return maybeJson;
                }
    
                if (maybeJson === null || typeof maybeJson === 'undefined') {
                    return [];
                }
    
                if (typeof maybeJson === 'string') {
                    var trimmed = maybeJson.trim();

                    if (! trimmed) {
                        return [];
                    }
    
                    var parsed = this.parseJsonString(trimmed);
                    var normalized = this.normalizeParsedToArrayOfStrings(parsed);

                    if (normalized) {
                        return normalized;
                    }
    
                    if (trimmed.indexOf(',') !== -1) {
                        return this.splitCommaSeparated(trimmed);
                    }
    
                    return [trimmed];
                }
    
                return [String(maybeJson)];
            }
        };
    
        var ErrorDisplay = {
            getErrorMessage: function(error) {
                if (error && error.response) {
                    if (error.response.data && error.response.data.message) {
                        return error.response.data.message;
                    }

                    if (error.response.data && error.response.data.error) {
                        return error.response.data.error;
                    }
    
                    return 'Server error: ' + error.response.status + ' ' + error.response.statusText;
                }
    
                if (error && error.request) {
                    return 'Network error: Unable to connect to server';
                }
    
                return (error && error.message) ? error.message : 'An unexpected error occurred';
            },
            showMainAlert: function(message, type) {
                var container = document.getElementById('alertContainer');
                var alertMessage = document.getElementById('alertMessage');

                if (! container || ! alertMessage) {
                    return;
                }
    
                container.classList.remove('hidden');
                alertMessage.children[0].textContent = message;
                alertMessage.className = 'px-4 py-3 rounded-lg border';
    
                var typeClasses = {
                    success: 'bg-green-100 border-green-200 text-green-700',
                    error: 'bg-red-100 border-red-200 text-red-700',
                    info: 'bg-blue-100 border-blue-200 text-blue-700'
                };

                var classesToAdd = (typeClasses[type] || typeClasses.info).split(' ');

                alertMessage.classList.add.apply(alertMessage.classList, classesToAdd);
    
                setTimeout(function() {
                    container.classList.add('hidden');
                    alertMessage.className = 'px-4 py-3 rounded-lg border';
                }, 6000);
    
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            showToast: function(message, type) {
                var existingToast = document.getElementById('modalToast');

                if (existingToast) {
                    existingToast.remove();
                }
    
                var toastElement = document.createElement('div');

                toastElement.id = 'modalToast';
                toastElement.className = 'fixed top-4 right-4 z-[70] px-4 py-3 rounded-lg border transform transition-all duration-300 translate-x-full max-w-sm shadow-sm';
    
                var typeClasses = {
                    success: 'bg-green-50 border-green-200 text-green-800',
                    error: 'bg-red-50 border-red-200 text-red-800',
                    info: 'bg-blue-50 border-blue-200 text-blue-800'
                };

                var cls = (typeClasses[type] || typeClasses.info).split(' ');

                cls.forEach(function(className) { toastElement.classList.add(className); });
    
                toastElement.innerHTML = '<div class="flex items-center gap-2"><div class="flex-1"><p class="font-medium text-xs">' + UtilityFunctions.escapeHtml(message) + '</p></div><button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">Close</button></div>';
                document.body.appendChild(toastElement);
    
                setTimeout(function() {
                    toastElement.classList.remove('translate-x-full');
                }, 100);
    
                setTimeout(function() {
                    if (toastElement.parentNode) {
                        toastElement.classList.add('translate-x-full');
                        setTimeout(function() {
                            toastElement.remove();
                        }, 300);
                    }
                }, 4000);
            }
        };
    
        function MultiSlugTagsInputComponent() {
            return {
                availableOptions: [],
                selectedValues: [],
                inputValue: '',
                initialize: function(initialOptions) {
                    if (Array.isArray(initialOptions)) {
                        this.availableOptions = initialOptions.slice();
                    } else {
                        this.availableOptions = [];
                    }
    
                    window.MultiSlugBox = this;
                },
                addFromInput: function() {
                    var cleanedValue = (this.inputValue || '').trim();

                    if (cleanedValue.length === 0) {
                        return;
                    }
    
                    if (!this.selectedValues.includes(cleanedValue)) {
                        this.selectedValues.push(cleanedValue);
                    }

                    if (!this.availableOptions.includes(cleanedValue)) {
                        this.availableOptions.push(cleanedValue);
                    }
    
                    this.inputValue = '';
                },
                addIfFilled: function() {
                    if ((this.inputValue || '').trim().length > 0) {
                        this.addFromInput();
                    }
                },
                removeByIndex: function(indexToRemove) {
                    if (indexToRemove >= 0 && indexToRemove < this.selectedValues.length) {
                        this.selectedValues.splice(indexToRemove, 1);
                    }
                },
                toggleValue: function(valueToToggle) {
                    var existingIndex = this.selectedValues.indexOf(valueToToggle);

                    if (existingIndex >= 0) {
                        this.selectedValues.splice(existingIndex, 1);
                    } else {
                        this.selectedValues.push(valueToToggle);
                    }
                },
                setValues: function(valuesToSet) {
                    var normalizedValues = Array.isArray(valuesToSet) ? valuesToSet : (valuesToSet ? [valuesToSet] : []);

                    this.selectedValues = [];

                    var self = this;
    
                    normalizedValues.forEach(function(singleValue) {
                        if (! self.availableOptions.includes(singleValue)) {
                            self.availableOptions.push(singleValue);
                        }

                        if (! self.selectedValues.includes(singleValue)) {
                            self.selectedValues.push(singleValue);
                        }
                    });
                }
            };
        }
    
        var FormValidation = {
            show: function(fieldName, message) {
                var errorElement = document.getElementById(fieldName + 'Error');
                var inputElement = document.getElementById(fieldName);

                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.classList.remove('hidden');
                }
    
                if (inputElement) {
                    inputElement.classList.add('input-error');
                }
            },
            showNone: function(fieldName) {
                var errorElement = document.getElementById(fieldName + 'Error');
                var inputElement = document.getElementById(fieldName);

                if (errorElement) {
                    errorElement.classList.add('hidden');
                }
    
                if (inputElement) {
                    inputElement.classList.remove('input-error');
                }
            },
            clearAll: function() {
                document.querySelectorAll('[id$="Error"]').forEach(function(errorNode) {
                    errorNode.classList.add('hidden');

                    var maybeInput = errorNode.previousElementSibling;
    
                    if (maybeInput && maybeInput.classList) {
                        maybeInput.classList.remove('input-error');
                    }
                });
            },
            fromServer: function(errorsObject) {
                Object.entries(errorsObject).forEach(function(pair) {
                    var fieldKey = pair[0];
                    var messages = pair[1];

                    if (Array.isArray(messages) && messages.length > 0) {
                        FormValidation.show(fieldKey, messages[0]);
                    }
                });
            }
        };
    
        var TemplateTable = {
            render: function() {
                if (TemplateApplicationState.templates.length === 0) {
                    this.showEmptyState();
                    return;
                }
    
                this.showTemplatesContainer();

                if (TemplateApplicationState.filteredTemplates.length === 0) {
                    this.showNoResults();
                    return;
                }
    
                this.hideNoResults();
                this.renderRows();
            },
            showEmptyState: function() {
                DocumentElements.tableBodyElement.innerHTML = '';
                DocumentElements.templatesContainerElement.classList.add('hidden');
                DocumentElements.emptyStateElement.classList.remove('hidden');
            },
            showTemplatesContainer: function() {
                DocumentElements.emptyStateElement.classList.add('hidden');
                DocumentElements.templatesContainerElement.classList.remove('hidden');
                DocumentElements.templatesContainerElement.className = 'bg-white rounded-lg border border-gray-200 shadow-sm';

                if (DocumentElements.templateCountElement) {
                    DocumentElements.templateCountElement.textContent = String(TemplateApplicationState.filteredTemplates.length);
                }
            },
            showNoResults: function() {
                DocumentElements.tableBodyElement.innerHTML = '';
            },
            hideNoResults: function() {
            },
            renderRows: function() {
                DocumentElements.tableBodyElement.innerHTML = TemplateApplicationState.filteredTemplates.map(function(templateItem) {
                    var pageArray = UtilityFunctions.toSlugArray(templateItem.page);
                    var slugChips = pageArray.map(function(singleSlug) {
                        return '<span class="mr-1 mb-1 inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800 border border-gray-200 text-xs">' + UtilityFunctions.escapeHtml(singleSlug) + '</span>';
                    }).join(' ');
    
                    var languageDisplay = UtilityFunctions.escapeHtml((templateItem.lang || '').toUpperCase());
                    var paperDisplay = UtilityFunctions.escapeHtml(UtilityFunctions.formatPaperSize(templateItem));
    
                    return '' +
                        '<tr class="hover:bg-gray-50 transition-colors">' +
                            '<td class="px-4 py-3"><div class="flex flex-wrap">' + slugChips + '</div></td>' +
                            '<td class="px-4 py-3"><span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">' + languageDisplay + '</span></td>' +
                            '<td class="px-4 py-3 text-gray-700 text-sm">' + paperDisplay + '</td>' +
                            '<td class="px-4 py-3">' +
                                '<div class="flex items-center gap-2">' +
                                    '<button onclick="TemplateModal.edit(' + templateItem.id + ')" class="px-3 py-1.5 border border-blue-200 text-blue-700 hover:bg-blue-50 rounded-lg transition-colors text-xs">Edit</button>' +
                                    '<button onclick="TemplateAPI.delete(' + templateItem.id + ')" class="px-3 py-1.5 border border-red-200 text-red-700 hover:bg-red-50 rounded-lg transition-colors text-xs">Delete</button>' +
                                '</div>' +
                            '</td>' +
                        '</tr>';
                }).join('');
            }
        };
    
        var TemplateModal = {
            open: function() {
                TemplateApplicationState.editingTemplate = null;
                DocumentElements.modalTitleElement.textContent = 'New Template';
                DocumentElements.submitTextElement.textContent = 'Save Template';
                DocumentElements.templateModalElement.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
    
                if (document.getElementById('templateId').value) {
                    this.resetForm();
                }
    
                if (window.MultiSlugBox) {
                    window.MultiSlugBox.setValues([]);
                }
    
                var hiddenJsonInput = document.getElementById('page');

                hiddenJsonInput.value = JSON.stringify([]);
            },
            close: function() {
                DocumentElements.templateModalElement.classList.add('hidden');
                document.body.style.overflow = 'auto';
            },
            edit: function(templateId) {
                var foundTemplate = TemplateApplicationState.templates.find(function(item) {
                    return item.id === templateId;
                });

                if (! foundTemplate) {
                    ErrorDisplay.showMainAlert('Template not found', 'error');
                    return;
                }
    
                TemplateApplicationState.editingTemplate = foundTemplate;
                DocumentElements.modalTitleElement.textContent = 'Edit Template';
                DocumentElements.submitTextElement.textContent = 'Update Template';
                this.populateForm(foundTemplate);
                DocumentElements.templateModalElement.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            },
            resetForm: function() {
                DocumentElements.templateFormElement.reset();
                document.getElementById('templateId').value = '';
                document.getElementById('password').value = '';
                FormValidation.clearAll();
            },
            populateForm: function(templateData) {
                var mappingObject = {
                    'templateId': templateData.id,
                    'lang': templateData.lang,
                    'disabled-smart-shrinking': templateData.disabled_smart_shrinking,
                    'paperSize': templateData.paper_size,
                    'orientation': templateData.orientation,
                    'marginTop': templateData.margin_top,
                    'marginBottom': templateData.margin_bottom,
                    'marginLeft': templateData.margin_left,
                    'marginRight': templateData.margin_right,
                    'headerSpace': templateData.header_space,
                    'footerSpace': templateData.footer_space,
                    'header': templateData.header,
                    'content': templateData.content,
                    'footer': templateData.footer
                };
    
                Object.entries(mappingObject).forEach(function(pair) {
                    var fieldId = pair[0];
                    var fieldValue = pair[1];
                    var fieldElement = document.getElementById(fieldId);

                    if (! fieldElement) {
                        return;
                    }
    
                    if (fieldElement.type === 'checkbox') {
                        fieldElement.checked = Boolean(fieldValue);
                    } else {
                        fieldElement.value = (fieldValue === null || typeof fieldValue === 'undefined') ? '' : fieldValue;
                    }
                });
    
                var pagesArray = UtilityFunctions.toSlugArray(templateData.page);

                if (window.MultiSlugBox) {
                    window.MultiSlugBox.setValues(pagesArray);
                }
    
                var hiddenJsonInput = document.getElementById('page');

                hiddenJsonInput.value = JSON.stringify(pagesArray);
            }
        };
    
        var TemplateAPI = {
            load: async function() {
                try {
                    var response = await HttpClientService.get('/{{ config('snawbar-invoice-template.route-prefix') }}/get-data');

                    TemplateApplicationState.templates = response.data || [];
                    TemplateApplicationState.filteredTemplates = TemplateApplicationState.templates.slice();
    
                    if (TemplateApplicationState.templates.length === 0) {
                        DocumentElements.templatesContainerElement.classList.add('hidden');
                        DocumentElements.emptyStateElement.classList.remove('hidden');
                    } else {
                        DocumentElements.templatesContainerElement.classList.remove('hidden');
                        DocumentElements.emptyStateElement.classList.add('hidden');
                    }
    
                    TemplateTable.render();
                }

                catch (error) {
                    ErrorDisplay.showMainAlert('Error loading templates: ' + ErrorDisplay.getErrorMessage(error), 'error');
                }
            },
            save: async function() {
                FormValidation.clearAll();
    
                var pageGuardElement = document.getElementById('page_required_guard');

                if (!pageGuardElement.value) {
                    FormValidation.show('page', 'At least one slug is required');
                    ErrorDisplay.showToast('Please add at least one slug', 'error');
                    return;
                }
    
                var languageElement = document.getElementById('lang');

                if (!languageElement.value.trim()) {
                    FormValidation.show('lang', 'This field is required');
                    ErrorDisplay.showToast('Please fill in all required fields', 'error');
                    return;
                }
    
                var submitButton = DocumentElements.submitButtonElement;
                var submitTextNode = DocumentElements.submitTextElement;

                submitButton.disabled = true;
                submitTextNode.textContent = TemplateApplicationState.editingTemplate ? 'Updating...' : 'Saving...';
    
                try {
                    var selectedPages = window.MultiSlugBox ? window.MultiSlugBox.selectedValues : [];
                    var hiddenJsonInput = document.getElementById('page');

                    hiddenJsonInput.value = JSON.stringify(selectedPages);
    
                    var formData = new FormData(DocumentElements.templateFormElement);

                    if (TemplateApplicationState.editingTemplate) {
                        formData.append('_method', 'PUT');
                        await HttpClientService.post('/{{ config('snawbar-invoice-template.route-prefix') }}/update/' + TemplateApplicationState.editingTemplate.id, formData);
                    } else {
                        await HttpClientService.post('/{{ config('snawbar-invoice-template.route-prefix') }}/store', formData);
                    }
    
                    var successMessage = TemplateApplicationState.editingTemplate ? 'Template updated successfully!' : 'Template created successfully!';

                    ErrorDisplay.showToast(successMessage, 'success');
                    TemplateModal.close();

                    await this.load();
                }

                catch (error) {
                    if (error.response && error.response.status === 422) {
                        var errorsBag = (error.response.data && error.response.data.errors) ? error.response.data.errors : {};
                        FormValidation.fromServer(errorsBag);
                        ErrorDisplay.showToast('Please fix the validation errors', 'error');
                    } else {
                        ErrorDisplay.showToast('Error saving template: ' + ErrorDisplay.getErrorMessage(error), 'error');
                    }
                }

                finally {
                    submitButton.disabled = false;
                    submitTextNode.textContent = TemplateApplicationState.editingTemplate ? 'Update Template' : 'Save Template';
                }
            },
            delete: async function(templateId) {
                var result = await Swal.fire({
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
                    await HttpClientService.delete('/{{ config('snawbar-invoice-template.route-prefix') }}/delete/' + templateId);

                    await Swal.fire({
                        title: 'Deleted!',
                        text: 'Template has been deleted.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    await this.load();
                }

                catch (error) {
                    await Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete template: ' + ErrorDisplay.getErrorMessage(error),
                        icon: 'error'
                    });
                }
            }
        };
    
        var KeyboardShortcuts = {
            initialize: function() {
                document.addEventListener('keydown', this.handleKeyDown.bind(this));
            },
            handleKeyDown: function(event) {
                if (event.key === 'Escape') {
                    if (!DocumentElements.templateModalElement.classList.contains('hidden')) {
                        TemplateModal.close();
                    }
                    return;
                }
    
                if (event.ctrlKey && (event.key === 's' || event.key === 'S') && !DocumentElements.templateModalElement.classList.contains('hidden')) {
                    event.preventDefault();
                    TemplateAPI.save();
                    return;
                }
            }
        };
    
        var Application = {
            initialize: async function() {
                try {
                    HttpClientService.initialize();
                    KeyboardShortcuts.initialize();

                    await TemplateAPI.load();
                }

                catch (error) {
                    ErrorDisplay.showMainAlert('Failed to initialize application: ' + ErrorDisplay.getErrorMessage(error), 'error');
                }
            }
        };
    
        document.addEventListener('DOMContentLoaded', function() {
            Application.initialize();
        });
    
        window.openModal = function() {
            TemplateModal.open();
        };

        window.closeModal = function() {
            TemplateModal.close();
        };

        window.editTemplate = function(id) {
            TemplateModal.edit(id);
        };

        window.deleteTemplate = function(id) {
            TemplateAPI.delete(id);
        };

        window.saveTemplate = function() {
            TemplateAPI.save();
        };
    </script>

</body>

</html>