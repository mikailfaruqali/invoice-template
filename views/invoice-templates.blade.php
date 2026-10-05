<!DOCTYPE html>
<html lang="en" data-theme="{{ $theme }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="{{ $theme }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Invoice Templates</title>
    @if (filled($favicon))
        <link rel="icon" href="{{ $favicon }}" />
    @endif

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css" />

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/mode/overlay.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closetag.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/display/placeholder.min.js"></script>

    <style>
        :root {
            color-scheme: light;
            --bg: #f5f6fa;
            --nav-bg: #ffffff;
            --nav-height: 56px;
            --nav-cap: 6px;
            --surface: #ffffff;
            --surface-2: #f5f6fa;
            --surface-3: #ebeef4;
            --line: #e5e9f2;
            --line-strong: #dbdfea;
            --text: #364a63;
            --text-2: #526484;
            --muted: #8094ae;
            --primary: #6576ff;
            --primary-hover: #5664d9;
            --primary-soft: rgba(101, 118, 255, 0.1);
            --danger: #e85347;
            --danger-soft: rgba(232, 83, 71, 0.1);
            --success: #1ee0ac;
            --success-soft: rgba(30, 224, 172, 0.12);
            --card-shadow: rgba(0, 0, 0, 0.08) 0 1px 4px;
            --overlay-shadow: 0 3px 12px 1px rgba(44, 55, 130, 0.15);
            --editor-bg: #101924;
            --editor-text: #e9eefb;
            --editor-muted: #5f6f86;
            --scrollbar: #cdd3e0;
            --focus-ring: 0 0 0 3px rgba(101, 118, 255, 0.18);
            --focus: #6576ff;
            --input-height: 36px;
            --input-bg: #ffffff;
            --input-border: #dbdfea;
            --input-text: #3c4d62;
            --input-placeholder: #b6c6e3;
            --s2-accent: #6576ff;
            --s2-accent-rgb: 101, 118, 255;
            --s2-highlight: #f1f3f8;
            --s2-divider: #eceff5;
            --s2-ring: rgba(16, 24, 40, 0.09);
            --s2-shadow: 0 16px 36px -10px rgba(16, 24, 40, 0.24), 0 4px 10px -4px rgba(16, 24, 40, 0.1);
            --tab-head: #f6f8fa;
            --tab-border: #d0d7de;
            --tab-text: #1f2328;
            --tab-muted: #59636e;
            --tab-accent: #0969da;
            --tab-active-bg: #ffffff;
            --tab-active-shadow: 0 1px 2px rgba(15, 23, 42, 0.08), 0 2px 8px -4px rgba(15, 23, 42, 0.12), 0 0 0 1px #d0d7de;
            --tab-hover: rgba(255, 255, 255, 0.55);
        }

        :root[data-theme='dark'] {
            color-scheme: dark;
            --bg: #0d141d;
            --nav-bg: #141c26;
            --surface: #141c26;
            --surface-2: #1b2532;
            --surface-3: #242e39;
            --line: #263241;
            --line-strong: #3d444d;
            --text: #e9eefb;
            --text-2: #b6c6e3;
            --muted: #8094ae;
            --primary-soft: rgba(101, 118, 255, 0.16);
            --danger-soft: rgba(232, 83, 71, 0.16);
            --success-soft: rgba(30, 224, 172, 0.14);
            --card-shadow: rgba(0, 0, 0, 0.19) 0 10px 20px, rgba(0, 0, 0, 0.23) 0 6px 6px;
            --overlay-shadow: 0 3px 20px 1px rgba(0, 0, 0, 0.4);
            --editor-bg: #0b1118;
            --scrollbar: rgba(139, 148, 158, 0.4);
            --focus-ring: 0 0 0 3px rgba(101, 118, 255, 0.28);
            --input-bg: #141c26;
            --input-border: #3d444d;
            --input-text: #b6c6e3;
            --input-placeholder: #8094ae;
            --s2-accent: #559bfb;
            --s2-accent-rgb: 85, 155, 251;
            --s2-highlight: rgba(255, 255, 255, 0.06);
            --s2-divider: rgba(255, 255, 255, 0.07);
            --s2-ring: rgba(255, 255, 255, 0.09);
            --s2-shadow: 0 18px 40px -8px rgba(0, 0, 0, 0.75), 0 4px 12px -4px rgba(0, 0, 0, 0.5);
            --tab-head: transparent;
            --tab-border: #2c3642;
            --tab-text: #c9d4e5;
            --tab-muted: #8094ae;
            --tab-accent: #58a6ff;
            --tab-active-bg: rgba(88, 166, 255, 0.13);
            --tab-active-shadow: none;
            --tab-hover: rgba(20, 28, 38, 0.7);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scrollbar-width: thin;
            scrollbar-color: var(--scrollbar) transparent;
        }

        html,
        body {
            height: 100%;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: Roboto, -apple-system, 'Segoe UI', 'Noto Sans Arabic', Tahoma, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        body.is-locked {
            overflow: hidden;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
            color: inherit;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--scrollbar);
            border-radius: 4px;
        }

        .icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            stroke-width: 1.9;
        }

        .icon-sm {
            width: 15px;
            height: 15px;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 20;
            height: var(--nav-height);
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 0 16px;
            background: var(--nav-bg);
            border-bottom: 1px solid var(--line);
        }

        .navbar::after {
            content: '';
            position: absolute;
            top: calc(100% + 1px);
            left: 0;
            right: 0;
            height: var(--nav-cap);
            background: var(--bg);
            pointer-events: none;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }

        .navbar-text {
            min-width: 0;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .brand-logo {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .navbar-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .navbar-subtitle {
            font-size: 12px;
            color: var(--muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .btn {
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 16px;
            border-radius: 4px;
            border: 1px solid transparent;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            transition:
                background 0.18s ease,
                border-color 0.18s ease,
                color 0.18s ease,
                box-shadow 0.18s ease;
        }

        .btn:focus-visible {
            outline: none;
            box-shadow: var(--focus-ring);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--primary-hover);
        }

        .btn-light {
            background: var(--surface-2);
            border-color: var(--line);
            color: var(--text);
        }

        .btn-light:hover {
            background: var(--surface-3);
        }

        .btn-icon {
            width: 38px;
            padding: 0;
        }

        .btn-sm {
            height: 32px;
            padding: 0 10px;
            font-size: 13px;
        }

        .btn-sm.btn-icon {
            width: 32px;
            padding: 0;
        }

        .btn-ghost-primary {
            background: transparent;
            color: var(--primary);
            border-color: var(--line);
        }

        .btn-ghost-primary:hover {
            background: var(--primary-soft);
        }

        .btn-ghost-danger {
            background: transparent;
            color: var(--danger);
            border-color: var(--line);
        }

        .btn-ghost-danger:hover {
            background: var(--danger-soft);
        }

        .search {
            position: relative;
            width: 260px;
        }

        .search .icon {
            position: absolute;
            top: 50%;
            inset-inline-start: 11px;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: var(--muted);
            pointer-events: none;
        }

        .input {
            display: block;
            width: 100%;
            height: var(--input-height);
            padding: 7px 14px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 4px;
            color: var(--input-text);
            line-height: 20px;
            transition:
                border-color 0.15s ease-in-out,
                box-shadow 0.15s ease-in-out;
        }

        .input::placeholder {
            color: var(--input-placeholder);
            opacity: 1;
        }

        .input:focus {
            outline: none;
            border-color: transparent;
            box-shadow: inset 0 0 0 2px var(--focus);
        }

        .search .input {
            padding-inline-start: 34px;
        }

        .page {
            max-width: 1280px;
            margin: 0 auto;
            padding: 16px;
        }

        .alert {
            display: none;
            margin-bottom: 16px;
            padding: 12px 16px;
            border-radius: 4px;
            border: 1px solid;
            font-size: 13px;
        }

        .alert.is-visible {
            display: block;
        }

        .alert-error {
            background: var(--danger-soft);
            border-color: var(--danger);
            color: var(--danger);
        }

        .alert-success {
            background: var(--success-soft);
            border-color: var(--success);
            color: var(--text);
        }

        .alert-info {
            background: var(--primary-soft);
            border-color: var(--primary);
            color: var(--text);
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 4px;
            box-shadow: var(--card-shadow);
        }

        .card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
        }

        .card-head-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text);
        }

        .count {
            min-width: 24px;
            height: 22px;
            padding: 0 8px;
            border-radius: 11px;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .table-wrap {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            padding: 10px 16px;
            text-align: start;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--muted);
            background: var(--surface-2);
            border-bottom: 1px solid var(--line);
            white-space: nowrap;
        }

        .table th:last-child {
            text-align: end;
        }

        .table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
            color: var(--text-2);
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table tbody tr {
            transition: background 0.15s ease;
        }

        .table tbody tr:hover {
            background: var(--surface-2);
        }

        .chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .chips-wide {
            gap: 12px;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            height: 24px;
            padding: 0 8px;
            border-radius: 4px;
            background: var(--surface-2);
            border: 1px solid var(--line);
            color: var(--text);
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
        }

        .chip-more {
            background: var(--primary-soft);
            border-color: transparent;
            color: var(--primary);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            height: 24px;
            padding: 0 8px;
            border-radius: 4px;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .part {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--text-2);
            white-space: nowrap;
        }

        .part::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--success);
        }

        .part.is-off {
            color: var(--muted);
            text-decoration: line-through;
        }

        .part.is-off::before {
            background: var(--line-strong);
        }

        .row-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
        }

        .empty {
            display: none;
            padding: 56px 16px;
            text-align: center;
        }

        .empty.is-visible {
            display: block;
        }

        .empty-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 16px;
            border-radius: 12px;
            background: var(--primary-soft);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-icon .icon {
            width: 26px;
            height: 26px;
        }

        .empty h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 4px;
        }

        .empty p {
            color: var(--muted);
            margin-bottom: 20px;
        }

        .editor {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: none;
            flex-direction: column;
            background: var(--bg);
        }

        .editor.is-open {
            display: flex;
        }

        .editor .navbar {
            position: relative;
            flex-shrink: 0;
        }

        .editor-body {
            flex: 1;
            min-height: 0;
            margin-top: var(--nav-cap);
            display: grid;
            grid-template-columns: 400px minmax(0, 1fr);
            gap: 16px;
            padding: 10px 16px 16px;
        }

        .settings {
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding-inline-end: 4px;
        }

        .section-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
        }

        .section-icon {
            width: 34px;
            height: 34px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: var(--primary-soft);
            color: var(--primary);
        }

        .section-icon.is-green {
            background: var(--success-soft);
            color: #10b981;
        }

        .section-icon.is-amber {
            background: rgba(244, 189, 14, 0.14);
            color: #d99e0b;
        }

        .section-icon.is-red {
            background: var(--danger-soft);
            color: var(--danger);
        }

        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
        }

        .section-hint {
            font-size: 12px;
            color: var(--muted);
        }

        .section-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .section-body.is-compact {
            gap: 8px;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-2);
        }

        .required {
            color: var(--danger);
        }

        .field-help {
            margin-top: 6px;
            font-size: 12px;
            color: var(--muted);
        }

        .field-error {
            display: none;
            margin-top: 6px;
            font-size: 12px;
            font-weight: 500;
            color: var(--danger);
        }

        .field-error.is-visible {
            display: block;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .subheading {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 10px;
        }

        .unit {
            position: relative;
        }

        .unit .input {
            padding-inline-end: 40px;
        }

        .unit span {
            position: absolute;
            top: 0;
            bottom: 0;
            inset-inline-end: 12px;
            display: flex;
            align-items: center;
            font-size: 12px;
            color: var(--muted);
            pointer-events: none;
        }

        .toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 4px;
            background: var(--surface);
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .toggle:hover {
            background: var(--surface-2);
        }

        .toggle-text strong {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text);
        }

        .toggle-text small {
            display: block;
            font-size: 12px;
            color: var(--muted);
        }

        .switch {
            position: relative;
            width: 40px;
            height: 22px;
            flex-shrink: 0;
        }

        .switch input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .switch-track {
            position: absolute;
            inset: 0;
            border-radius: 11px;
            background: var(--line-strong);
            transition: background 0.18s ease;
        }

        .switch-track::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
            transition: transform 0.18s ease;
        }

        .switch input:checked + .switch-track {
            background: var(--primary);
        }

        .switch input:checked + .switch-track::after {
            transform: translateX(18px);
        }

        .switch input:focus-visible + .switch-track {
            box-shadow: var(--focus-ring);
        }

        .workspace {
            min-height: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .tabs {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            gap: 6px;
            min-height: 58px;
            padding: 0 12px;
            background: var(--tab-head);
            border-bottom: 1px solid var(--tab-border);
            border-radius: 4px 4px 0 0;
            overflow-x: auto;
            overflow-y: hidden;
            flex-shrink: 0;
            scrollbar-width: none;
        }

        .tabs::-webkit-scrollbar {
            display: none;
        }

        .tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            height: 38px;
            padding: 0 14px;
            border: 0;
            border-radius: 4px;
            background: transparent;
            color: var(--tab-muted);
            font-size: 15px;
            font-weight: 500;
            white-space: nowrap;
            cursor: pointer;
            transition:
                background 0.15s ease,
                color 0.15s ease;
        }

        .tab:hover {
            background: var(--tab-hover);
            color: var(--tab-text);
        }

        .tab.is-active {
            background: var(--tab-active-bg);
            color: var(--tab-accent);
            box-shadow: var(--tab-active-shadow);
        }

        .tab.has-error {
            color: var(--danger);
        }

        .panel {
            display: none;
            flex: 1;
            min-height: 0;
            flex-direction: column;
        }

        .panel.is-active {
            display: flex;
        }

        .panel-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px 16px;
            min-height: 48px;
            padding: 8px 16px;
            border-bottom: 1px solid var(--line);
            font-size: 12px;
            color: var(--muted);
            flex-shrink: 0;
        }

        .panel-bar code {
            padding: 1px 6px;
            border-radius: 4px;
            background: var(--surface-2);
            border: 1px solid var(--line);
            color: var(--text);
            font-size: 11px;
        }

        .opacity {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .opacity .input {
            width: 84px;
            height: 32px;
            padding: 0 10px;
        }

        .code {
            flex: 1;
            width: 100%;
            min-height: 0;
            padding: 16px;
            border: none;
            border-radius: 0 0 4px 4px;
            background: var(--editor-bg);
            color: var(--editor-text);
            font-family: 'JetBrains Mono', 'Cascadia Code', Consolas, 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.7;
            tab-size: 4;
            font-variant-ligatures: none;
            font-feature-settings: 'liga' 0, 'calt' 0;
            resize: none;
            outline: none;
            direction: ltr;
            text-align: left;
        }

        .code::placeholder {
            color: var(--editor-muted);
        }

        .panel .CodeMirror {
            flex: 1 1 0;
            min-height: 0;
            height: auto;
            border-radius: 0 0 4px 4px;
        }

        .cm-s-invoice.CodeMirror {
            background: var(--editor-bg);
            color: var(--editor-text);
            font-family: 'JetBrains Mono', 'Cascadia Code', Consolas, 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.7;
            font-variant-ligatures: none;
            direction: ltr;
        }

        .cm-s-invoice .CodeMirror-lines {
            padding: 12px 0;
        }

        .cm-s-invoice .CodeMirror-gutters {
            background: var(--editor-bg);
            border-right: 1px solid rgba(255, 255, 255, 0.06);
        }

        .cm-s-invoice .CodeMirror-linenumber {
            padding: 0 12px 0 8px;
            color: #4b5a6e;
        }

        .cm-s-invoice .CodeMirror-cursor {
            border-left: 2px solid #79c0ff;
        }

        .cm-s-invoice .CodeMirror-selected,
        .cm-s-invoice.CodeMirror-focused .CodeMirror-selected {
            background: rgba(101, 118, 255, 0.3);
        }

        .cm-s-invoice pre.CodeMirror-line,
        .cm-s-invoice pre.CodeMirror-line-like {
            font-variant-ligatures: none;
            font-feature-settings: 'liga' 0, 'calt' 0;
        }

        .cm-s-invoice pre.CodeMirror-placeholder {
            color: var(--editor-muted);
        }

        .cm-s-invoice .CodeMirror-matchingbracket {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.14);
        }

        .cm-s-invoice .cm-tag {
            color: #7ee787;
        }

        .cm-s-invoice .cm-attribute,
        .cm-s-invoice .cm-property {
            color: #79c0ff;
        }

        .cm-s-invoice .cm-string,
        .cm-s-invoice .cm-string-2 {
            color: #a5d6ff;
        }

        .cm-s-invoice .cm-comment,
        .cm-s-invoice .cm-meta {
            color: #8b949e;
            font-style: italic;
        }

        .cm-s-invoice .cm-bracket {
            color: #8b949e;
        }

        .cm-s-invoice .cm-number,
        .cm-s-invoice .cm-atom,
        .cm-s-invoice .cm-variable-2 {
            color: #ffa657;
        }

        .cm-s-invoice .cm-keyword {
            color: #ff7b72;
        }

        .cm-s-invoice .cm-def,
        .cm-s-invoice .cm-qualifier,
        .cm-s-invoice .cm-builtin,
        .cm-s-invoice .cm-variable-3,
        .cm-s-invoice .cm-type {
            color: #d2a8ff;
        }

        .cm-s-invoice .cm-variable {
            color: #e6edf3;
        }

        .cm-s-invoice .cm-error {
            color: #ffa198;
        }

        .cm-s-invoice .cm-blade-echo {
            color: #ffa657;
            background: rgba(255, 166, 87, 0.1);
        }

        .cm-s-invoice .cm-blade-directive {
            color: #ff7b72;
            font-weight: 600;
        }

        .cm-s-invoice .cm-blade-token {
            color: #f2cc60;
            background: rgba(242, 204, 96, 0.12);
        }

        .cm-s-invoice .cm-blade-comment {
            color: #8b949e;
            font-style: italic;
        }

        .CodeMirror.input-error {
            box-shadow: inset 0 0 0 2px var(--danger);
        }

        .panel .field-error {
            margin: 0;
            padding: 8px 16px;
        }

        .input-error {
            border-color: var(--danger) !important;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-selection--single,
        .select2-container .select2-selection--multiple {
            position: relative;
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            height: var(--input-height) !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding-block: 0 !important;
            padding-inline: 12px 34px !important;
            overflow: hidden !important;
            background: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
            border-radius: 4px !important;
            box-shadow: none !important;
            outline: none !important;
            color: var(--input-text);
            cursor: pointer;
            transition:
                border-color 0.18s ease,
                box-shadow 0.18s ease;
        }

        .select2-container .select2-selection--multiple {
            padding-inline-end: 28px !important;
        }

        .select2-container--open .select2-selection--single,
        .select2-container--open .select2-selection--multiple,
        .select2-container--focus .select2-selection--single,
        .select2-container--focus .select2-selection--multiple {
            border-color: transparent !important;
            box-shadow: inset 0 0 0 1.5px var(--focus) !important;
        }

        .select2-container .select2-selection__arrow {
            display: none !important;
        }

        .select2-container .select2-selection--single::after,
        .select2-container .select2-selection--multiple::after {
            content: '';
            position: absolute;
            top: 50%;
            inset-inline-end: 12px;
            width: 12px;
            height: 12px;
            background-color: var(--muted);
            transform: translateY(-50%);
            pointer-events: none;
            transition:
                transform 0.2s ease,
                background-color 0.18s ease;
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath d='M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z'/%3E%3C/svg%3E") center / contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath d='M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z'/%3E%3C/svg%3E") center / contain no-repeat;
        }

        .select2-container--open .select2-selection--single::after,
        .select2-container--open .select2-selection--multiple::after {
            background-color: var(--s2-accent);
            transform: translateY(-50%) rotate(180deg);
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            flex: 1;
            min-width: 0;
            padding: 0 !important;
            overflow: hidden;
            color: var(--input-text) !important;
            line-height: 1.4 !important;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .select2-container .select2-selection__placeholder {
            color: var(--input-placeholder) !important;
        }

        .select2-container .select2-selection--multiple .select2-selection__rendered {
            display: flex !important;
            flex: 1;
            flex-wrap: nowrap !important;
            align-items: center;
            gap: 5px;
            min-width: 0;
            margin: 0 !important;
            margin-inline-end: 6px !important;
            padding: 0 !important;
            overflow: hidden !important;
            list-style: none;
            flex: 0 1 auto !important;
        }

        .select2-container .select2-selection--multiple .select2-selection__rendered:empty {
            display: none !important;
        }

        .select2-container .select2-selection__clear {
            position: absolute !important;
            top: 50%;
            inset-inline-end: 30px;
            z-index: 1;
            display: flex !important;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            border: 0 !important;
            border-radius: 50% !important;
            font-size: 0 !important;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .select2-container .select2-selection__clear::after {
            content: '';
            display: block;
            width: 10px;
            height: 10px;
            background-color: var(--muted);
            transition: background-color 0.15s ease;
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 384 512'%3E%3Cpath d='M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z'/%3E%3C/svg%3E") center / contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 384 512'%3E%3Cpath d='M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z'/%3E%3C/svg%3E") center / contain no-repeat;
        }

        .select2-container .select2-selection__clear:hover::after {
            background-color: var(--danger);
        }

        .select2-container .select2-selection--single:has(.select2-selection__clear) {
            padding-inline-end: 54px !important;
        }

        .select2-container .select2-selection--multiple .select2-selection__clear {
            inset-inline-end: 28px !important;
            width: 28px !important;
            border-inline-start: 1px solid var(--input-border) !important;
            border-radius: 0 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice.is-overflow {
            display: none !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__more {
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
            height: 24px;
            padding: 0 8px;
            border-radius: 4px;
            background: var(--s2-highlight);
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
        }

        .select2-container .select2-selection--multiple,
        .select2-container .select2-selection--multiple * {
            cursor: pointer !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            display: inline-flex !important;
            align-items: center;
            flex-shrink: 0;
            max-width: 180px;
            height: 24px;
            margin: 0 !important;
            padding: 0 !important;
            padding-inline-end: 8px !important;
            overflow: hidden;
            background: rgba(var(--s2-accent-rgb), 0.1) !important;
            border: 1px solid rgba(var(--s2-accent-rgb), 0.22) !important;
            border-radius: 4px !important;
            color: var(--s2-accent) !important;
            font-size: 13px;
            font-weight: 500;
            line-height: 22px;
            white-space: nowrap;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: static !important;
            display: inline !important;
            float: none !important;
            padding: 0 4px 0 6px !important;
            background: none !important;
            border: none !important;
            color: rgba(var(--s2-accent-rgb), 0.7) !important;
            font-size: 14px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover,
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:focus {
            background: none !important;
            color: var(--danger) !important;
            outline: none !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
            display: inline-block;
            min-width: 0;
            padding: 0 !important;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .select2-container .select2-selection--multiple .select2-search--inline {
            display: inline-flex !important;
            flex: 1 1 0;
            min-width: 0;
            align-items: center;
            margin: 0 !important;
            margin-inline-end: 32px !important;
        }

        .select2-container .select2-selection--multiple:has(.select2-selection__choice) .select2-search--inline {
            margin-inline-start: 0 !important;
        }

        .select2-container .select2-selection--multiple .select2-search--inline .select2-search__field {
            width: 100% !important;
            height: 24px !important;
            min-width: 2px;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
            color: var(--input-text);
            font-size: inherit !important;
            font-family: inherit;
        }

        .select2-container .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder {
            color: var(--input-placeholder);
        }

        .select2-dropdown {
            z-index: 9999 !important;
            margin-top: 6px;
            padding: 0 !important;
            overflow: hidden;
            background: var(--surface) !important;
            border: 0 !important;
            border-radius: 4px !important;
            box-shadow:
                0 0 0 1px var(--s2-ring),
                var(--s2-shadow) !important;
            animation: s2-in 0.14s ease-out;
        }

        .select2-dropdown--above {
            margin-top: -6px;
            animation-name: s2-in-up;
        }

        @keyframes s2-in {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }
        }

        @keyframes s2-in-up {
            from {
                opacity: 0;
                transform: translateY(4px);
            }
        }

        .select2-search--dropdown {
            position: relative;
            padding: 6px !important;
            border-bottom: 1px solid var(--s2-divider) !important;
        }

        .select2-search--dropdown.select2-search--hide {
            border-bottom: 0 !important;
        }

        .select2-search--dropdown .select2-search__field {
            display: block;
            width: 100% !important;
            height: 36px !important;
            margin: 0 !important;
            padding-block: 0 !important;
            padding-inline: 32px 10px !important;
            background: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
            border-radius: 4px !important;
            box-shadow: none !important;
            color: var(--input-text) !important;
            outline: none !important;
        }

        .select2-search--dropdown .select2-search__field:focus {
            border-color: transparent !important;
            box-shadow: inset 0 0 0 1.5px var(--focus) !important;
        }

        .select2-search--dropdown::before {
            content: '';
            position: absolute;
            top: 50%;
            inset-inline-start: 17px;
            z-index: 1;
            width: 13px;
            height: 13px;
            background-color: var(--muted);
            transform: translateY(-50%);
            pointer-events: none;
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath d='M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z'/%3E%3C/svg%3E") center / contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'%3E%3Cpath d='M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z'/%3E%3C/svg%3E") center / contain no-repeat;
        }

        .select2-search--dropdown.select2-search--hide::before {
            display: none;
        }

        .select2-search--dropdown:focus-within::before {
            background-color: var(--focus);
        }

        .select2-results__options {
            max-height: 280px !important;
            padding: 5px !important;
            overflow-x: hidden;
            overflow-y: auto !important;
            overscroll-behavior: contain;
        }

        .select2-results__option {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 !important;
            padding: 6px 10px !important;
            line-height: 1.4;
            background: transparent !important;
            border-radius: 4px !important;
            color: var(--input-text) !important;
            cursor: pointer;
            transition: background-color 0.1s ease;
        }

        .select2-results__option--highlighted,
        .select2-results__option--highlighted[aria-selected] {
            background: var(--s2-highlight) !important;
            color: var(--input-text) !important;
        }

        .select2-results__option--selected,
        .select2-results__option[aria-selected='true'] {
            font-weight: 600;
        }

        .select2-results__option--selected::after,
        .select2-results__option[aria-selected='true']::after {
            content: '';
            flex-shrink: 0;
            width: 13px;
            height: 13px;
            margin-inline-start: auto;
            background-color: var(--s2-accent);
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'%3E%3Cpath d='M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z'/%3E%3C/svg%3E") center / contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'%3E%3Cpath d='M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z'/%3E%3C/svg%3E") center / contain no-repeat;
        }

        .select2-results__message {
            justify-content: center;
            padding: 12px 10px !important;
            color: var(--muted) !important;
            cursor: default;
        }

        .toast {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 80;
            max-width: min(380px, calc(100vw - 32px));
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 4px;
            border: 1px solid var(--line);
            border-left: 3px solid var(--primary);
            background: var(--surface);
            color: var(--text);
            box-shadow: var(--overlay-shadow);
            font-size: 13px;
            transform: translateY(-12px);
            opacity: 0;
            transition:
                transform 0.25s ease,
                opacity 0.25s ease;
        }

        .toast.is-visible {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-success {
            border-left-color: var(--success);
        }

        .toast-error {
            border-left-color: var(--danger);
        }

        .toast-message {
            flex: 1;
        }

        .toast button {
            border: none;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            display: flex;
        }

        .swal2-popup {
            border-radius: 6px !important;
            font-family: inherit !important;
        }

        .is-hidden {
            display: none !important;
        }

        @media (max-width: 1023px) {
            .editor-body {
                display: block;
                overflow-y: auto;
                overscroll-behavior: contain;
            }

            .settings {
                overflow: visible;
                padding-inline-end: 0;
                margin-bottom: 16px;
            }

            .workspace {
                height: 75vh;
                min-height: 440px;
            }
        }

        @media (max-width: 767px) {
            .navbar {
                padding: 0 12px;
                gap: 8px;
            }

            .navbar-subtitle,
            .only-desktop {
                display: none !important;
            }

            .page {
                padding: 12px 8px;
            }

            .editor-body {
                padding: 10px 8px 12px;
            }

            .card-head {
                flex-direction: column;
                align-items: stretch;
            }

            .search {
                width: 100%;
            }

            .tabs {
                min-height: 52px;
                padding: 0 10px;
            }

            .tab {
                padding: 0 12px;
            }

            .table thead {
                display: none;
            }

            .table,
            .table tbody,
            .table tr,
            .table td {
                display: block;
                width: 100%;
            }

            .table tbody tr {
                padding: 12px 14px;
                border-bottom: 1px solid var(--line);
            }

            .table tbody tr:last-child {
                border-bottom: none;
            }

            .table td {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 6px 0;
                border: none;
            }

            .table td::before {
                content: attr(data-label);
                font-size: 11px;
                font-weight: 600;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                color: var(--muted);
                flex-shrink: 0;
            }

            .table td .chips {
                justify-content: flex-end;
            }

            .table td[data-label='Actions'] {
                padding-top: 10px;
            }
        }
    </style>
</head>

<body>
    <header class="navbar">
        <div class="navbar-brand">
            @if (filled($favicon))
                <img class="brand-logo" src="{{ $favicon }}" alt="" />
            @else
                <span class="brand-icon">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                </span>
            @endif
            <div class="navbar-text">
                <div class="navbar-title">Invoice Templates</div>
                <div class="navbar-subtitle">Create and manage your PDF templates</div>
            </div>
        </div>
        <div class="navbar-actions">
            <button type="button" class="btn btn-primary" onclick="TemplateModal.open()">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                <span class="only-desktop">New Template</span>
            </button>
        </div>
    </header>

    <main class="page">
        <div id="alertContainer" class="alert"></div>

        <section id="templatesContainer" class="card is-hidden">
            <div class="card-head">
                <div class="card-head-title">
                    <h2 class="card-title">Templates</h2>
                    <span id="templateCount" class="count">0</span>
                </div>
                <label class="search">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input
                        id="templateSearch"
                        type="search"
                        class="input"
                        placeholder="Search page or language"
                        oninput="TemplateTable.filter(this.value)"
                    />
                </label>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pages</th>
                            <th>Language</th>
                            <th>Paper</th>
                            <th>Parts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="templatesTableBody"></tbody>
                </table>
            </div>

            <div id="noResults" class="empty">
                <h3>No matching templates</h3>
                <p>Try a different page or language.</p>
            </div>
        </section>

        <section id="emptyState" class="card empty">
            <div class="empty-icon">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="12" y1="18" x2="12" y2="12" />
                    <line x1="9" y1="15" x2="15" y2="15" />
                </svg>
            </div>
            <h3>No templates yet</h3>
            <p>Create your first template to get started.</p>
            <button type="button" class="btn btn-primary" onclick="TemplateModal.open()">Create Template</button>
        </section>
    </main>

    <div id="templateModal" class="editor" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <header class="navbar">
            <button type="button" class="btn btn-light btn-icon" onclick="TemplateModal.close()" aria-label="Close">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
            <div class="navbar-brand">
                <div class="navbar-text">
                    <div id="modalTitle" class="navbar-title">New Template</div>
                    <div class="navbar-subtitle">Ctrl + S to save · Esc to close</div>
                </div>
            </div>
            <div class="navbar-actions">
                <button type="button" class="btn btn-light only-desktop" onclick="TemplateModal.close()">Cancel</button>
                <button type="button" id="submitBtn" class="btn btn-primary" onclick="TemplateAPI.save()">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                        <polyline points="17 21 17 13 7 13 7 21" />
                        <polyline points="7 3 7 8 15 8" />
                    </svg>
                    <span id="submitText">Save</span>
                </button>
            </div>
        </header>

        <form id="templateForm" class="editor-body" onsubmit="return false;">
            <input type="hidden" id="templateId" name="id" />
            <input type="hidden" id="page" name="page" />

            <div class="settings">
                <section class="card">
                    <div class="section-head">
                        <span class="section-icon">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                                <line x1="7" y1="7" x2="7.01" y2="7" />
                            </svg>
                        </span>
                        <div>
                            <div class="section-title">General</div>
                            <div class="section-hint">Which pages and language use it</div>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="field">
                            <label for="pageSlugs">Pages <span class="required">*</span></label>
                            <select id="pageSlugs" multiple></select>
                            <div class="field-help">Pick a page or type a new slug and press Enter</div>
                            <div id="pageError" class="field-error"></div>
                        </div>
                        <div class="field">
                            <label for="lang">Language <span class="required">*</span></label>
                            <select id="lang" name="lang"></select>
                            <div class="field-help">Use * to match every language</div>
                            <div id="langError" class="field-error"></div>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="section-head">
                        <span class="section-icon is-green">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" />
                                <path d="M3 9h18" />
                                <path d="M3 15h18" />
                            </svg>
                        </span>
                        <div>
                            <div class="section-title">Page Layout</div>
                            <div class="section-hint">Paper, margins and spacing in mm</div>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="grid-2">
                            <div class="field">
                                <label for="paperSize">Paper Size</label>
                                <select id="paperSize" name="paper_size">
                                    <option value="A4">A4</option>
                                    <option value="A5">A5</option>
                                    <option value="A3">A3</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="orientation">Orientation</label>
                                <select id="orientation" name="orientation">
                                    <option value="portrait">Portrait</option>
                                    <option value="landscape">Landscape</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <div class="subheading">Margins</div>
                            <div class="grid-2">
                                <div class="field">
                                    <label for="marginTop">Top</label>
                                    <div class="unit">
                                        <input
                                            type="number"
                                            id="marginTop"
                                            name="margin_top"
                                            step="0.1"
                                            value="20"
                                            class="input"
                                        />
                                        <span>mm</span>
                                    </div>
                                </div>
                                <div class="field">
                                    <label for="marginBottom">Bottom</label>
                                    <div class="unit">
                                        <input
                                            type="number"
                                            id="marginBottom"
                                            name="margin_bottom"
                                            step="0.1"
                                            value="20"
                                            class="input"
                                        />
                                        <span>mm</span>
                                    </div>
                                </div>
                                <div class="field">
                                    <label for="marginLeft">Left</label>
                                    <div class="unit">
                                        <input
                                            type="number"
                                            id="marginLeft"
                                            name="margin_left"
                                            step="0.1"
                                            value="20"
                                            class="input"
                                        />
                                        <span>mm</span>
                                    </div>
                                </div>
                                <div class="field">
                                    <label for="marginRight">Right</label>
                                    <div class="unit">
                                        <input
                                            type="number"
                                            id="marginRight"
                                            name="margin_right"
                                            step="0.1"
                                            value="20"
                                            class="input"
                                        />
                                        <span>mm</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="subheading">Spacing</div>
                            <div class="grid-2">
                                <div class="field">
                                    <label for="headerSpace">Header</label>
                                    <div class="unit">
                                        <input
                                            type="number"
                                            id="headerSpace"
                                            name="header_space"
                                            step="0.1"
                                            value="10"
                                            class="input"
                                        />
                                        <span>mm</span>
                                    </div>
                                </div>
                                <div class="field">
                                    <label for="footerSpace">Footer</label>
                                    <div class="unit">
                                        <input
                                            type="number"
                                            id="footerSpace"
                                            name="footer_space"
                                            step="0.1"
                                            value="10"
                                            class="input"
                                        />
                                        <span>mm</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="section-head">
                        <span class="section-icon is-amber">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" y1="21" x2="4" y2="14" />
                                <line x1="4" y1="10" x2="4" y2="3" />
                                <line x1="12" y1="21" x2="12" y2="12" />
                                <line x1="12" y1="8" x2="12" y2="3" />
                                <line x1="20" y1="21" x2="20" y2="16" />
                                <line x1="20" y1="12" x2="20" y2="3" />
                                <line x1="1" y1="14" x2="7" y2="14" />
                                <line x1="9" y1="8" x2="15" y2="8" />
                                <line x1="17" y1="16" x2="23" y2="16" />
                            </svg>
                        </span>
                        <div>
                            <div class="section-title">Options</div>
                            <div class="section-hint">Turn parts of the document off</div>
                        </div>
                    </div>
                    <div class="section-body is-compact">
                        <label class="toggle">
                            <span class="toggle-text">
                                <strong>Disable Smart Shrinking</strong>
                                <small>Keep wide content at its real size</small>
                            </span>
                            <span class="switch">
                                <input type="checkbox" id="disabled-smart-shrinking" name="disabled_smart_shrinking" />
                                <span class="switch-track"></span>
                            </span>
                        </label>
                        <label class="toggle">
                            <span class="toggle-text">
                                <strong>Disable Header</strong>
                                <small>Print pages without the header</small>
                            </span>
                            <span class="switch">
                                <input type="checkbox" id="disable-header" name="disable_header" />
                                <span class="switch-track"></span>
                            </span>
                        </label>
                        <label class="toggle">
                            <span class="toggle-text">
                                <strong>Disable Footer</strong>
                                <small>Print pages without the footer</small>
                            </span>
                            <span class="switch">
                                <input type="checkbox" id="disable-footer" name="disable_footer" />
                                <span class="switch-track"></span>
                            </span>
                        </label>
                        <label class="toggle">
                            <span class="toggle-text">
                                <strong>Disable Watermark</strong>
                                <small>Keep the watermark but don't print it</small>
                            </span>
                            <span class="switch">
                                <input type="checkbox" id="disable-watermark" name="disable_watermark" />
                                <span class="switch-track"></span>
                            </span>
                        </label>
                    </div>
                </section>

                <section class="card">
                    <div class="section-head">
                        <span class="section-icon is-red">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </span>
                        <div>
                            <div class="section-title">Security</div>
                            <div class="section-hint">Content changes need the template password</div>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="field">
                            <label for="password">Password <span class="required">*</span></label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="input"
                                placeholder="Template password"
                                autocomplete="current-password"
                                oninput="FormValidation.showNone('password')"
                            />
                            <div id="passwordError" class="field-error"></div>
                        </div>
                    </div>
                </section>
            </div>

            <section class="card workspace">
                <div class="tabs" role="tablist">
                    <button
                        type="button"
                        class="tab is-active"
                        data-tab="header"
                        onclick="EditorTabs.activate('header')"
                    >
                        Header
                    </button>
                    <button type="button" class="tab" data-tab="content" onclick="EditorTabs.activate('content')">
                        Content
                    </button>
                    <button type="button" class="tab" data-tab="footer" onclick="EditorTabs.activate('footer')">
                        Footer
                    </button>
                    <button type="button" class="tab" data-tab="watermark" onclick="EditorTabs.activate('watermark')">
                        Watermark
                    </button>
                </div>

                <div class="panel is-active" data-panel="header">
                    <div class="panel-bar">
                        <span>Blade HTML drawn inside the top margin of every page</span>
                        <span><code>{PAGENO}</code> <code>{TOPAGE}</code></span>
                    </div>
                    <textarea
                        id="header"
                        name="header"
                        class="code"
                        spellcheck="false"
                        placeholder="<div>Company name and logo...</div>"
                        oninput="FormValidation.showNone('header')"
                    ></textarea>
                    <div id="headerError" class="field-error"></div>
                </div>

                <div class="panel" data-panel="content">
                    <div class="panel-bar">
                        <span>Blade HTML for the main body of the document</span>
                    </div>
                    <textarea
                        id="content"
                        name="content"
                        class="code"
                        spellcheck="false"
                        placeholder="<table>Invoice lines...</table>"
                        oninput="FormValidation.showNone('content')"
                    ></textarea>
                    <div id="contentError" class="field-error"></div>
                </div>

                <div class="panel" data-panel="footer">
                    <div class="panel-bar">
                        <span>Blade HTML drawn inside the bottom margin of every page</span>
                        <span><code>{PAGENO}</code> <code>{TOPAGE}</code></span>
                    </div>
                    <textarea
                        id="footer"
                        name="footer"
                        class="code"
                        spellcheck="false"
                        placeholder="<div>Page {PAGENO} of {TOPAGE}</div>"
                        oninput="FormValidation.showNone('footer')"
                    ></textarea>
                    <div id="footerError" class="field-error"></div>
                </div>

                <div class="panel" data-panel="watermark">
                    <div class="panel-bar">
                        <span>Blade HTML stamped over every page</span>
                        <label class="opacity" for="watermarkOpacity">
                            Opacity
                            <input
                                type="number"
                                id="watermarkOpacity"
                                name="watermark_opacity"
                                step="0.05"
                                min="0"
                                max="1"
                                value="0.3"
                                class="input"
                            />
                        </label>
                    </div>
                    <textarea
                        id="watermark"
                        name="watermark"
                        class="code"
                        spellcheck="false"
                        placeholder="<div>PAID</div>"
                        oninput="FormValidation.showNone('watermark')"
                    ></textarea>
                    <div id="watermarkError" class="field-error"></div>
                </div>
            </section>
        </form>
    </div>

    <script>
                var INITIAL_PAGE_SLUGS = @json($pageSlugs);
                var ROUTE_PREFIX = '/{{ config('snawbar-invoice-template.route-prefix') }}';

                var TemplateApplicationState = {
                    templates: [],
                    filteredTemplates: [],
                    editingTemplate: null,
                    searchTerm: '',
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
                    get noResultsElement() {
                        return document.getElementById('noResults');
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
                    },
                };

                var HttpClientService = {
                    initialize: function () {
                        var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');

                        if (csrfTokenMeta) {
                            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfTokenMeta.getAttribute('content');
                        }
                    },
                    get: function (url, config) {
                        return axios.get(url, config || {});
                    },
                    post: function (url, data, config) {
                        return axios.post(url, data, config || {});
                    },
                    delete: function (url, config) {
                        return axios.delete(url, config || {});
                    },
                };

                var UtilityFunctions = {
                    capitalize: function (inputString) {
                        if (!inputString || typeof inputString !== 'string') {
                            return '';
                        }

                        return inputString.charAt(0).toUpperCase() + inputString.slice(1);
                    },
                    escapeHtml: function (inputText) {
                        var temporaryDiv = document.createElement('div');

                        temporaryDiv.textContent = inputText === null || typeof inputText === 'undefined' ? '' : String(inputText);

                        return temporaryDiv.innerHTML;
                    },
                    toSlugArray: function (maybeJson) {
                        if (Array.isArray(maybeJson)) {
                            return maybeJson.map(String);
                        }

                        if (maybeJson === null || typeof maybeJson === 'undefined') {
                            return [];
                        }

                        var trimmed = String(maybeJson).trim();

                        if (!trimmed) {
                            return [];
                        }

                        try {
                            var parsed = JSON.parse(trimmed);

                            if (Array.isArray(parsed)) {
                                return parsed.map(String);
                            }

                            if (typeof parsed === 'string') {
                                return [parsed];
                            }
                        } catch (error) {}

                        return trimmed
                            .split(',')
                            .map(function (value) {
                                return value.trim();
                            })
                            .filter(Boolean);
                    },
                    unique: function (values) {
                        return values.filter(function (value, index) {
                            return value !== '' && values.indexOf(value) === index;
                        });
                    },
                    cssVariable: function (name) {
                        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
                    },
                };

                var ErrorDisplay = {
                    getErrorMessage: function (error) {
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

                        return error && error.message ? error.message : 'An unexpected error occurred';
                    },
                    showMainAlert: function (message, type) {
                        var container = document.getElementById('alertContainer');

                        container.className = 'alert is-visible alert-' + (type || 'info');
                        container.textContent = message;

                        setTimeout(function () {
                            container.className = 'alert';
                        }, 6000);
                    },
                    showToast: function (message, type) {
                        var existingToast = document.getElementById('modalToast');

                        if (existingToast) {
                            existingToast.remove();
                        }

                        var toastElement = document.createElement('div');

                        toastElement.id = 'modalToast';
                        toastElement.className = 'toast toast-' + (type || 'info');
                        toastElement.innerHTML =
                            '<span class="toast-message">' +
                            UtilityFunctions.escapeHtml(message) +
                            '</span><button type="button" aria-label="Close" onclick="this.parentElement.remove()">' +
                            '<svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
                            '</button>';

                        document.body.appendChild(toastElement);

                        requestAnimationFrame(function () {
                            toastElement.classList.add('is-visible');
                        });

                        setTimeout(function () {
                            toastElement.classList.remove('is-visible');

                            setTimeout(function () {
                                toastElement.remove();
                            }, 250);
                        }, 4000);
                    },
                    dialog: function (options) {
                        return Swal.fire(
                            Object.assign(
                                {
                                    background: UtilityFunctions.cssVariable('--surface'),
                                    color: UtilityFunctions.cssVariable('--text'),
                                    confirmButtonColor: UtilityFunctions.cssVariable('--primary'),
                                    cancelButtonColor: UtilityFunctions.cssVariable('--muted'),
                                },
                                options,
                            ),
                        );
                    },
                };

                var FormValidation = {
                    show: function (fieldName, message) {
                        var errorElement = document.getElementById(fieldName + 'Error');
                        var inputElement = document.getElementById(fieldName);

                        if (errorElement) {
                            errorElement.textContent = message;
                            errorElement.classList.add('is-visible');
                        }

                        if (!inputElement) {
                            return;
                        }

                        (CodeEditors.wrapper(fieldName) || inputElement).classList.add('input-error');

                        var panel = inputElement.closest('[data-panel]');

                        if (panel) {
                            EditorTabs.activate(panel.getAttribute('data-panel'));
                            EditorTabs.markError(panel.getAttribute('data-panel'));
                        }
                    },
                    showNone: function (fieldName) {
                        var errorElement = document.getElementById(fieldName + 'Error');
                        var inputElement = document.getElementById(fieldName);

                        if (errorElement) {
                            errorElement.classList.remove('is-visible');
                        }

                        if (inputElement) {
                            inputElement.classList.remove('input-error');
                        }

                        if (CodeEditors.wrapper(fieldName)) {
                            CodeEditors.wrapper(fieldName).classList.remove('input-error');
                            EditorTabs.clearError(fieldName);
                        }
                    },
                    clearAll: function () {
                        document.querySelectorAll('.field-error').forEach(function (errorNode) {
                            errorNode.classList.remove('is-visible');
                        });

                        document.querySelectorAll('.input-error').forEach(function (inputNode) {
                            inputNode.classList.remove('input-error');
                        });

                        document.querySelectorAll('.tab.has-error').forEach(function (tabNode) {
                            tabNode.classList.remove('has-error');
                        });
                    },
                    fromServer: function (errorsObject) {
                        Object.entries(errorsObject).forEach(function (pair) {
                            if (Array.isArray(pair[1]) && pair[1].length > 0) {
                                FormValidation.show(pair[0], pair[1][0]);
                            }
                        });
                    },
                };

                var EditorTabs = {
                    activate: function (name) {
                        document.querySelectorAll('.tab').forEach(function (tab) {
                            tab.classList.toggle('is-active', tab.getAttribute('data-tab') === name);
                        });

                        document.querySelectorAll('.panel').forEach(function (panel) {
                            panel.classList.toggle('is-active', panel.getAttribute('data-panel') === name);
                        });

                        CodeEditors.refresh(name);
                    },
                    clearError: function (name) {
                        var tab = document.querySelector('.tab[data-tab="' + name + '"]');

                        if (tab) {
                            tab.classList.remove('has-error');
                        }
                    },
                    markError: function (name) {
                        var tab = document.querySelector('.tab[data-tab="' + name + '"]');

                        if (tab) {
                            tab.classList.add('has-error');
                        }
                    },
                };

                var CodeEditors = {
                    fields: ['header', 'content', 'footer', 'watermark'],
                    instances: {},
                    initialize: function () {
                        CodeMirror.defineMode('blade-html', function (config) {
                            return CodeMirror.overlayMode(CodeMirror.getMode(config, 'htmlmixed'), {
                                token: function (stream) {
                                    if (stream.match(/\{\{--.*?--\}\}/)) {
                                        return 'blade-comment';
                                    }

                                    if (stream.match(/\{!!.*?!!\}/) || stream.match(/\{\{.*?\}\}/)) {
                                        return 'blade-echo';
                                    }

                                    if (stream.match(/\{(PAGENO|TOPAGE)\}/)) {
                                        return 'blade-token';
                                    }

                                    if (stream.sol() || /\s/.test(stream.string.charAt(stream.start - 1) || ' ')) {
                                        if (stream.match(/@[a-zA-Z]+/)) {
                                            return 'blade-directive';
                                        }
                                    }

                                    stream.next();

                                    return null;
                                },
                            });
                        });

                        this.fields.forEach(function (name) {
                            var textarea = document.getElementById(name);
                            var editor = CodeMirror.fromTextArea(textarea, {
                                mode: 'blade-html',
                                theme: 'invoice',
                                lineNumbers: true,
                                lineWrapping: true,
                                indentUnit: 4,
                                tabSize: 4,
                                autoCloseTags: true,
                                matchBrackets: true,
                            });

                            editor.on('change', function () {
                                editor.save();
                                FormValidation.showNone(name);
                            });

                            CodeEditors.instances[name] = editor;
                        });
                    },
                    wrapper: function (name) {
                        return this.instances[name] ? this.instances[name].getWrapperElement() : null;
                    },
                    load: function () {
                        this.fields.forEach(function (name) {
                            var editor = CodeEditors.instances[name];

                            editor.setValue(document.getElementById(name).value);
                            editor.clearHistory();
                        });
                    },
                    save: function () {
                        this.fields.forEach(function (name) {
                            CodeEditors.instances[name].save();
                        });
                    },
                    refresh: function (name) {
                        var editor = this.instances[name];

                        if (editor) {
                            setTimeout(function () {
                                editor.refresh();
                            });
                        }
                    },
                };

                var SelectFields = {
                    initialize: function () {
                        var dropdownParent = $('#templateModal');

                        $('#pageSlugs')
                            .select2({
                                tags: true,
                                allowClear: true,
                                tokenSeparators: [',', ' '],
                                placeholder: 'Select or type page slugs',
                                width: '100%',
                                dropdownParent: dropdownParent,
                            })
                            .on('change', function () {
                                FormValidation.showNone('page');
                            });

                        document.querySelectorAll('.select2-selection--multiple .select2-selection__rendered').forEach(function (list) {
                            new MutationObserver(function (records) {
                                var redrawn = records.some(function (record) {
                                    return Array.prototype.some.call(record.addedNodes, function (node) {
                                        return !node.classList || !node.classList.contains('select2-selection__more');
                                    });
                                });

                                if (redrawn) {
                                    SelectFields.fitChoices();
                                }
                            }).observe(list, { childList: true });
                        });

                        window.addEventListener('resize', function () {
                            SelectFields.fitChoices();
                        });

                        $('#lang')
                            .select2({
                                tags: true,
                                allowClear: true,
                                placeholder: 'Select or type a language code',
                                width: '100%',
                                dropdownParent: dropdownParent,
                            })
                            .on('change', function () {
                                FormValidation.showNone('lang');
                            });

                        $('#paperSize, #orientation').select2({
                            minimumResultsForSearch: Infinity,
                            width: '100%',
                            dropdownParent: dropdownParent,
                        });
                    },
                    ensureOption: function (select, value) {
                        var exists = select.find('option').filter(function () {
                            return this.value === value;
                        }).length;

                        if (!exists) {
                            select.append(new Option(value, value, false, false));
                        }
                    },
                    setOptions: function (selector, values) {
                        var select = $(selector);
                        var current = select.val();

                        select.empty();

                        values.forEach(function (value) {
                            select.append(new Option(value, value, false, false));
                        });

                        select.val(current).trigger('change.select2');
                    },
                    setValue: function (selector, value) {
                        var select = $(selector);
                        var values = (Array.isArray(value) ? value : [value])
                            .filter(function (singleValue) {
                                return singleValue !== null && typeof singleValue !== 'undefined' && singleValue !== '';
                            })
                            .map(String);

                        values.forEach(function (singleValue) {
                            SelectFields.ensureOption(select, singleValue);
                        });

                        select.val(select.prop('multiple') ? values : values.length ? values[0] : null).trigger('change');
                    },
                    fitChoices: function () {
                        $('.select2-selection--multiple .select2-selection__rendered').each(function () {
                            var list = this;
                            var choices = Array.prototype.slice.call(list.querySelectorAll('.select2-selection__choice'));
                            var existing = list.querySelector('.select2-selection__more');

                            if (existing) {
                                existing.remove();
                            }

                            choices.forEach(function (choice) {
                                choice.classList.remove('is-overflow');
                            });

                            if (!choices.length || list.scrollWidth <= list.clientWidth) {
                                return;
                            }

                            var available = list.clientWidth - 44;
                            var used = 0;
                            var hidden = 0;

                            choices.forEach(function (choice, index) {
                                used += choice.offsetWidth + (index ? 5 : 0);

                                if (used > available && index > 0) {
                                    choice.classList.add('is-overflow');
                                    hidden++;
                                }
                            });

                            if (hidden) {
                                var more = document.createElement('li');

                                more.className = 'select2-selection__more';
                                more.textContent = '+' + hidden;
                                list.appendChild(more);
                            }
                        });
                    },
                    refreshOptions: function () {
                        var slugs = INITIAL_PAGE_SLUGS.map(String);
                        var languages = ['*'];

                        TemplateApplicationState.templates.forEach(function (templateItem) {
                            slugs = slugs.concat(UtilityFunctions.toSlugArray(templateItem.page));
                            languages.push(String(templateItem.lang || ''));
                        });

                        this.setOptions('#pageSlugs', UtilityFunctions.unique(slugs));
                        this.setOptions('#lang', UtilityFunctions.unique(languages));
                    },
                };

                var TemplateTable = {
                    render: function () {
                        var hasTemplates = TemplateApplicationState.templates.length > 0;
                        var rows = TemplateApplicationState.filteredTemplates;

                        DocumentElements.templatesContainerElement.classList.toggle('is-hidden', !hasTemplates);
                        DocumentElements.emptyStateElement.classList.toggle('is-visible', !hasTemplates);
                        DocumentElements.templateCountElement.textContent = String(rows.length);
                        DocumentElements.noResultsElement.classList.toggle('is-visible', hasTemplates && rows.length === 0);
                        DocumentElements.tableBodyElement.innerHTML = rows.map(this.renderRow).join('');
                    },
                    filter: function (term) {
                        TemplateApplicationState.searchTerm = (term || '').trim().toLowerCase();

                        var search = TemplateApplicationState.searchTerm;

                        TemplateApplicationState.filteredTemplates = TemplateApplicationState.templates.filter(function (templateItem) {
                            if (!search) {
                                return true;
                            }

                            return UtilityFunctions.toSlugArray(templateItem.page)
                                .concat([templateItem.lang])
                                .join(' ')
                                .toLowerCase()
                                .indexOf(search) !== -1;
                        });

                        this.render();
                    },
                    renderRow: function (templateItem) {
                        var pages = UtilityFunctions.toSlugArray(templateItem.page);
                        var pageChips = pages
                            .slice(0, 4)
                            .map(function (slug) {
                                return '<span class="chip">' + UtilityFunctions.escapeHtml(slug) + '</span>';
                            })
                            .join('');

                        if (pages.length > 4) {
                            pageChips +=
                                '<span class="chip chip-more" title="' +
                                UtilityFunctions.escapeHtml(pages.slice(4).join(', ')) +
                                '">+' +
                                (pages.length - 4) +
                                '</span>';
                        }

                        var parts = [
                            ['Header', Number(templateItem.disable_header) !== 1 && templateItem.header],
                            ['Footer', Number(templateItem.disable_footer) !== 1 && templateItem.footer],
                            ['Watermark', Number(templateItem.disable_watermark) !== 1 && templateItem.watermark],
                        ]
                            .map(function (part) {
                                return '<span class="part' + (part[1] ? '' : ' is-off') + '">' + part[0] + '</span>';
                            })
                            .join('');

                        return (
                            '<tr>' +
                            '<td data-label="Pages"><div class="chips">' +
                            pageChips +
                            '</div></td>' +
                            '<td data-label="Language"><span class="badge">' +
                            UtilityFunctions.escapeHtml(templateItem.lang) +
                            '</span></td>' +
                            '<td data-label="Paper">' +
                            UtilityFunctions.escapeHtml(templateItem.paper_size) +
                            ' · ' +
                            UtilityFunctions.escapeHtml(UtilityFunctions.capitalize(templateItem.orientation)) +
                            '</td>' +
                            '<td data-label="Parts"><div class="chips chips-wide">' +
                            parts +
                            '</div></td>' +
                            '<td data-label="Actions"><div class="row-actions">' +
                            '<button type="button" class="btn btn-sm btn-ghost-primary" onclick="TemplateModal.edit(' +
                            templateItem.id +
                            ')"><svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>Edit</button>' +
                            '<button type="button" class="btn btn-sm btn-icon btn-ghost-danger" aria-label="Delete" onclick="TemplateAPI.delete(' +
                            templateItem.id +
                            ')"><svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>' +
                            '</div></td>' +
                            '</tr>'
                        );
                    },
                };

                var TemplateModal = {
                    open: function () {
                        TemplateApplicationState.editingTemplate = null;
                        DocumentElements.modalTitleElement.textContent = 'New Template';
                        DocumentElements.submitTextElement.textContent = 'Save';
                        this.resetForm();
                        this.show();
                    },
                    close: function () {
                        DocumentElements.templateModalElement.classList.remove('is-open');
                        document.body.classList.remove('is-locked');
                    },
                    show: function () {
                        DocumentElements.templateModalElement.classList.add('is-open');
                        document.body.classList.add('is-locked');
                        EditorTabs.activate('header');
                        SelectFields.fitChoices();
                    },
                    isOpen: function () {
                        return DocumentElements.templateModalElement.classList.contains('is-open');
                    },
                    edit: function (templateId) {
                        var foundTemplate = TemplateApplicationState.templates.find(function (item) {
                            return item.id === templateId;
                        });

                        if (!foundTemplate) {
                            ErrorDisplay.showMainAlert('Template not found', 'error');
                            return;
                        }

                        TemplateApplicationState.editingTemplate = foundTemplate;
                        DocumentElements.modalTitleElement.textContent = 'Edit Template';
                        DocumentElements.submitTextElement.textContent = 'Update';
                        this.resetForm();
                        this.populateForm(foundTemplate);
                        this.show();
                    },
                    resetForm: function () {
                        DocumentElements.templateFormElement.reset();
                        document.getElementById('templateId').value = '';
                        document.getElementById('page').value = JSON.stringify([]);
                        SelectFields.setValue('#pageSlugs', []);
                        SelectFields.setValue('#lang', null);
                        SelectFields.setValue('#paperSize', 'A4');
                        SelectFields.setValue('#orientation', 'portrait');
                        CodeEditors.load();
                        FormValidation.clearAll();
                    },
                    populateForm: function (templateData) {
                        var mappingObject = {
                            templateId: templateData.id,
                            'disabled-smart-shrinking': templateData.disabled_smart_shrinking,
                            'disable-header': templateData.disable_header,
                            'disable-footer': templateData.disable_footer,
                            'disable-watermark': templateData.disable_watermark,
                            marginTop: templateData.margin_top,
                            marginBottom: templateData.margin_bottom,
                            marginLeft: templateData.margin_left,
                            marginRight: templateData.margin_right,
                            headerSpace: templateData.header_space,
                            footerSpace: templateData.footer_space,
                            header: templateData.header,
                            content: templateData.content,
                            footer: templateData.footer,
                            watermark: templateData.watermark,
                            watermarkOpacity: templateData.watermark_opacity,
                        };

                        Object.entries(mappingObject).forEach(function (pair) {
                            var fieldElement = document.getElementById(pair[0]);
                            var fieldValue = pair[1];

                            if (!fieldElement) {
                                return;
                            }

                            if (fieldElement.type === 'checkbox') {
                                fieldElement.checked = Boolean(Number(fieldValue));
                            } else {
                                fieldElement.value = fieldValue === null || typeof fieldValue === 'undefined' ? '' : fieldValue;
                            }
                        });

                        var pagesArray = UtilityFunctions.toSlugArray(templateData.page);

                        SelectFields.setValue('#pageSlugs', pagesArray);
                        SelectFields.setValue('#lang', templateData.lang);
                        SelectFields.setValue('#paperSize', templateData.paper_size);
                        SelectFields.setValue('#orientation', templateData.orientation);
                        CodeEditors.load();
                        document.getElementById('page').value = JSON.stringify(pagesArray);
                    },
                };

                var TemplateAPI = {
                    load: async function () {
                        try {
                            var response = await HttpClientService.get(ROUTE_PREFIX + '/get-data');

                            TemplateApplicationState.templates = response.data || [];
                            SelectFields.refreshOptions();
                            TemplateTable.filter(TemplateApplicationState.searchTerm);
                        } catch (error) {
                            ErrorDisplay.showMainAlert('Error loading templates: ' + ErrorDisplay.getErrorMessage(error), 'error');
                        }
                    },
                    save: async function () {
                        FormValidation.clearAll();

                        var selectedPages = $('#pageSlugs').val() || [];

                        if (selectedPages.length === 0) {
                            FormValidation.show('page', 'At least one page is required');
                            ErrorDisplay.showToast('Please add at least one page', 'error');
                            return;
                        }

                        if (!$('#lang').val()) {
                            FormValidation.show('lang', 'This field is required');
                            ErrorDisplay.showToast('Please choose a language', 'error');
                            return;
                        }

                        var submitButton = DocumentElements.submitButtonElement;
                        var submitTextNode = DocumentElements.submitTextElement;
                        var isEditing = Boolean(TemplateApplicationState.editingTemplate);

                        submitButton.disabled = true;
                        submitTextNode.textContent = isEditing ? 'Updating...' : 'Saving...';

                        try {
                            document.getElementById('page').value = JSON.stringify(selectedPages);
                            CodeEditors.save();

                            var formData = new FormData(DocumentElements.templateFormElement);

                            if (isEditing) {
                                formData.append('_method', 'PUT');
                                await HttpClientService.post(ROUTE_PREFIX + '/update/' + TemplateApplicationState.editingTemplate.id, formData);
                            } else {
                                await HttpClientService.post(ROUTE_PREFIX + '/store', formData);
                            }

                            ErrorDisplay.showToast(isEditing ? 'Template updated successfully!' : 'Template created successfully!', 'success');
                            TemplateModal.close();

                            await this.load();
                        } catch (error) {
                            if (error.response && error.response.status === 422) {
                                FormValidation.fromServer((error.response.data && error.response.data.errors) || {});
                                ErrorDisplay.showToast('Please fix the validation errors', 'error');
                            } else {
                                ErrorDisplay.showToast('Error saving template: ' + ErrorDisplay.getErrorMessage(error), 'error');
                            }
                        } finally {
                            submitButton.disabled = false;
                            submitTextNode.textContent = isEditing ? 'Update' : 'Save';
                        }
                    },
                    delete: async function (templateId) {
                        var result = await ErrorDisplay.dialog({
                            title: 'Delete template?',
                            text: 'This action cannot be undone.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: UtilityFunctions.cssVariable('--danger'),
                            confirmButtonText: 'Yes, delete it',
                            cancelButtonText: 'Cancel',
                        });

                        if (!result.isConfirmed) {
                            return;
                        }

                        try {
                            await HttpClientService.delete(ROUTE_PREFIX + '/delete/' + templateId);

                            ErrorDisplay.showToast('Template deleted', 'success');

                            await this.load();
                        } catch (error) {
                            await ErrorDisplay.dialog({
                                title: 'Error',
                                text: 'Failed to delete template: ' + ErrorDisplay.getErrorMessage(error),
                                icon: 'error',
                            });
                        }
                    },
                };

                var KeyboardShortcuts = {
                    initialize: function () {
                        document.addEventListener('keydown', this.handleKeyDown.bind(this));
                    },
                    handleKeyDown: function (event) {
                        if (!TemplateModal.isOpen()) {
                            return;
                        }

                        if (event.key === 'Escape' && !document.querySelector('.select2-container--open')) {
                            TemplateModal.close();
                            return;
                        }

                        if (event.ctrlKey && (event.key === 's' || event.key === 'S')) {
                            event.preventDefault();
                            TemplateAPI.save();
                        }
                    },
                };

                var Application = {
                    initialize: async function () {
                        HttpClientService.initialize();
                        KeyboardShortcuts.initialize();
                        SelectFields.initialize();
                        SelectFields.refreshOptions();
                        CodeEditors.initialize();

                        await TemplateAPI.load();
                    },
                };

                document.addEventListener('DOMContentLoaded', function () {
                    Application.initialize();
                });

                window.openModal = function () {
                    TemplateModal.open();
                };

                window.closeModal = function () {
                    TemplateModal.close();
                };

                window.editTemplate = function (id) {
                    TemplateModal.edit(id);
                };

                window.deleteTemplate = function (id) {
                    TemplateAPI.delete(id);
                };

                window.saveTemplate = function () {
                    TemplateAPI.save();
                };
            </script>
        </body>
        </html>

