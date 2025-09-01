<?php

namespace Snawbar\InvoiceTemplate\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Snawbar\InvoiceTemplate\Traits\DatabaseOperations;

class InvoiceTemplateController extends Controller
{
    use DatabaseOperations;

    public function index()
    {
        return view('snawbar-invoice-template::invoice-templates', [
            'pageSlugs' => collect(config('snawbar-invoice-template.page-slugs'))
                ->unique()
                ->values()
                ->toArray(),
        ]);
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
            'header' => 'nullable|string',
            'content' => 'nullable|string',
            'footer' => 'nullable|string',
            'margin_top' => 'numeric|min:0',
            'margin_bottom' => 'numeric|min:0',
            'margin_left' => 'numeric|min:0',
            'margin_right' => 'numeric|min:0',
            'header_space' => 'numeric|min:0',
            'footer_space' => 'numeric|min:0',
            'orientation' => 'in:portrait,landscape',
            'paper_size' => 'in:A4,A5,A3',
        ]);

        $this->validatePasswordForContentChange($request);

        return $this->create($request);
    }

    public function update(Request $request, $templateId)
    {
        $request->validate([
            'page' => 'required',
            'lang' => 'required',
            'header' => 'nullable|string',
            'content' => 'nullable|string',
            'footer' => 'nullable|string',
            'margin_top' => 'numeric|min:0',
            'margin_bottom' => 'numeric|min:0',
            'margin_left' => 'numeric|min:0',
            'margin_right' => 'numeric|min:0',
            'header_space' => 'numeric|min:0',
            'footer_space' => 'numeric|min:0',
            'orientation' => 'in:portrait,landscape',
            'paper_size' => 'in:A4,A5,A3',
        ]);

        $this->validatePasswordForContentChange($request);

        return $this->create($request, $templateId);
    }

    public function destroy($templateId)
    {
        return $this->deleteTemplate($templateId);
    }

    private function isContentChanged(Request $request, $templateId = NULL)
    {
        if (blank($templateId) || blank($original = $this->getTemplate($templateId))) {
            return $this->hasAnyContent($request);
        }

        return collect(['content', 'header', 'footer'])->contains(fn ($field) => trim($original->{$field}) !== trim($request->input($field)));
    }

    private function hasAnyContent(Request $request)
    {
        return collect(['content', 'header', 'footer'])->contains(fn ($field) => filled($request->input($field)));
    }

    private function validatePasswordForContentChange(Request $request)
    {
        throw_if($this->isContentChanged($request) && ! $this->isValidPassword($request->password), ValidationException::withMessages([
            'password' => 'The password is incorrect',
        ]));
    }

    private function isValidPassword($password): bool
    {
        return password_verify($password, config('snawbar-invoice-template.password'));
    }
}
