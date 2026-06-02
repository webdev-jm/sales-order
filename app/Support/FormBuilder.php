<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\HtmlString;

class FormBuilder
{
    public function __construct(
        protected UrlGenerator $url,
        protected Session $session,
    ) {}

    public function open(array $options = []): HtmlString
    {
        $method     = strtoupper($options['method'] ?? 'POST');
        $htmlMethod = in_array($method, ['GET', 'POST']) ? $method : 'POST';
        $spoofed    = ! in_array($method, ['GET', 'POST']);

        $action = $this->resolveAction($options);

        $attrs = ['method' => $htmlMethod, 'action' => $action, 'accept-charset' => 'UTF-8'];

        if (! empty($options['enctype'])) {
            $attrs['enctype'] = $options['enctype'];
        } elseif (! empty($options['files'])) {
            $attrs['enctype'] = 'multipart/form-data';
        }

        foreach (['id', 'class', 'autocomplete', 'novalidate', 'target'] as $attr) {
            if (isset($options[$attr])) {
                $attrs[$attr] = $options[$attr];
            }
        }

        $html = '<form' . $this->attributesToString($attrs) . '>';

        if ($htmlMethod !== 'GET') {
            $token = $this->session->token();
            $html .= "\n<input type=\"hidden\" name=\"_token\" value=\"{$token}\">";
        }

        if ($spoofed) {
            $html .= "\n<input type=\"hidden\" name=\"_method\" value=\"{$method}\">";
        }

        return new HtmlString($html);
    }

    public function close(): HtmlString
    {
        return new HtmlString('</form>');
    }

    public function text(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('text', $name, $value, $options);
    }

    public function email(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('email', $name, $value, $options);
    }

    public function password(string $name, array $options = []): HtmlString
    {
        return $this->input('password', $name, null, $options);
    }

    public function number(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('number', $name, $value, $options);
    }

    public function hidden(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('hidden', $name, $value, $options);
    }

    public function file(string $name, array $options = []): HtmlString
    {
        return $this->input('file', $name, null, $options);
    }

    public function date(string $name, $value = null, array $options = []): HtmlString
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }

        return $this->input('date', $name, $value, $options);
    }

    public function time(string $name, $value = null, array $options = []): HtmlString
    {
        return $this->input('time', $name, $value, $options);
    }

    public function checkbox(string $name, $value = 1, $checked = null, array $options = []): HtmlString
    {
        $attrs = array_merge($options, ['type' => 'checkbox', 'name' => $name, 'value' => $value]);
        $attrs = $this->withDefaultId($name, $attrs);

        if ($checked) {
            $attrs['checked'] = 'checked';
        }

        return new HtmlString('<input' . $this->attributesToString($attrs) . '>');
    }

    public function radio(string $name, $value = null, $checked = null, array $options = []): HtmlString
    {
        $attrs = array_merge($options, ['type' => 'radio', 'name' => $name, 'value' => $value]);
        $attrs = $this->withDefaultId($name, $attrs);

        if ($checked) {
            $attrs['checked'] = 'checked';
        }

        return new HtmlString('<input' . $this->attributesToString($attrs) . '>');
    }

    public function textarea(string $name, $value = null, array $options = []): HtmlString
    {
        $attrs = array_merge($options, ['name' => $name]);
        $attrs = $this->withDefaultId($name, $attrs);

        if (! isset($attrs['rows'])) {
            $attrs['rows'] = 10;
        }

        if (! isset($attrs['cols'])) {
            $attrs['cols'] = 50;
        }

        $attrString = $this->attributesToString($attrs);
        $content    = e((string) ($value ?? ''));

        return new HtmlString("<textarea{$attrString}>{$content}</textarea>");
    }

    public function select(string $name, array $list = [], $selected = null, array $options = []): HtmlString
    {
        $attrs = array_merge($options, ['name' => $name]);
        $attrs = $this->withDefaultId($name, $attrs);
        $attrString  = $this->attributesToString($attrs);
        $optionsHtml = $this->buildOptions($list, $selected);

        return new HtmlString("<select{$attrString}>{$optionsHtml}</select>");
    }

    public function label(string $for, string $value = '', array $options = []): HtmlString
    {
        $attrs = array_merge($options, ['for' => $for]);
        $attrString = $this->attributesToString($attrs);
        $content    = e($value);

        return new HtmlString("<label{$attrString}>{$content}</label>");
    }

    public function submit(string $value, array $options = []): HtmlString
    {
        $attrs = array_merge($options, ['type' => 'submit', 'value' => $value]);

        return new HtmlString('<input' . $this->attributesToString($attrs) . '>');
    }

    protected function input(string $type, string $name, $value, array $options): HtmlString
    {
        $attrs = array_merge($options, ['type' => $type, 'name' => $name]);
        $attrs = $this->withDefaultId($name, $attrs);

        if ($type !== 'password' && $value !== null) {
            $attrs['value'] = $value;
        }

        return new HtmlString('<input' . $this->attributesToString($attrs) . '>');
    }

    protected function withDefaultId(string $name, array $attrs): array
    {
        if (! isset($attrs['id'])) {
            $attrs['id'] = $name;
        }

        return $attrs;
    }

    protected function resolveAction(array $options): string
    {
        if (isset($options['route'])) {
            $route = $options['route'];

            if (is_array($route)) {
                $name = array_shift($route);
                return $this->url->route($name, $route);
            }

            return $this->url->route($route);
        }

        if (isset($options['action'])) {
            $action = $options['action'];

            if (is_array($action)) {
                return $this->url->action($action[0], array_slice($action, 1));
            }

            return $this->url->action($action);
        }

        if (isset($options['url'])) {
            return $this->url->to($options['url']);
        }

        return $this->url->current();
    }

    protected function buildOptions(array $list, $selected): string
    {
        $html = '';

        foreach ($list as $key => $value) {
            if (is_array($value)) {
                $groupAttrs = $this->attributesToString(['label' => $key]);
                $inner      = $this->buildOptions($value, $selected);
                $html .= "<optgroup{$groupAttrs}>{$inner}</optgroup>";
            } else {
                $attrs      = ['value' => $key];
                $isSelected = is_array($selected)
                    ? in_array((string) $key, array_map('strval', $selected))
                    : ((string) $key === (string) $selected);

                if ($isSelected) {
                    $attrs['selected'] = 'selected';
                }

                $label = e((string) $value);
                $html .= '<option' . $this->attributesToString($attrs) . ">{$label}</option>";
            }
        }

        return $html;
    }

    protected function attributesToString(array $attrs): string
    {
        $parts = [];

        foreach ($attrs as $key => $value) {
            if (is_int($key)) {
                $parts[] = e((string) $value);
                continue;
            }

            if ($value === true) {
                $parts[] = e($key);
                continue;
            }

            if ($value === false || $value === null) {
                continue;
            }

            $parts[] = e($key) . '="' . e((string) $value) . '"';
        }

        return $parts ? ' ' . implode(' ', $parts) : '';
    }
}
