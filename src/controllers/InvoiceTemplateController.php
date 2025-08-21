<?php

namespace Snawbar\InvoiceTemplate\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Snawbar\InvoiceTemplate\Traits\DatabaseOperations;

class InvoiceTemplateController extends Controller
{
    use DatabaseOperations;

    public function index()
    {
        return view('snawbar-invoice-template::invoice-templates');
    }

    public function getData()
    {
        return response()->json($this->getAllTemplates());
    }

    public function store(Request $request)
    {
        $request->validate([
            'page' => 'required',
            'lang' => 'required',
            'header' => 'required|string',
            'content' => 'required|string',
            'footer' => 'nullable|string',
            'margin_top' => 'numeric|min:0',
            'margin_bottom' => 'numeric|min:0',
            'margin_left' => 'numeric|min:0',
            'margin_right' => 'numeric|min:0',
            'header_space' => 'numeric|min:0',
            'footer_space' => 'numeric|min:0',
            'orientation' => 'in:portrait,landscape',
            'paper_size' => 'in:A4,A5,A3',
            'width' => 'numeric|min:1',
            'height' => 'numeric|min:1',
        ]);

        return $this->create($request);
    }

    public function update(Request $request, $templateId)
    {
        $request->validate([
            'page' => 'required',
            'lang' => 'required',
            'header' => 'required|string',
            'content' => 'required|string',
            'footer' => 'nullable|string',
            'margin_top' => 'numeric|min:0',
            'margin_bottom' => 'numeric|min:0',
            'margin_left' => 'numeric|min:0',
            'margin_right' => 'numeric|min:0',
            'header_space' => 'numeric|min:0',
            'footer_space' => 'numeric|min:0',
            'orientation' => 'in:portrait,landscape',
            'paper_size' => 'in:A4,A5,A3',
            'width' => 'numeric|min:1',
            'height' => 'numeric|min:1',
        ]);

        return $this->create($request, $templateId);
    }

    public function destroy($templateId)
    {
        return $this->deleteTemplate($templateId);
    }
}
