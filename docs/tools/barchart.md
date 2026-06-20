# Bar chart

`Console::barChart([label => value, ...])` — a labelled, responsive, themed
horizontal bar chart.

```php
echo Console::barChart(['API' => 1240, 'Web' => 860, 'CLI' => 320])->render();
```

Bars scale to the largest value and to the available width. Setters:
`add($label, $value)`, `width($n)`, `responsive($bool)`, `showValues($bool)`.
Glyphs degrade `█/░` → `#/-` without Unicode; colours follow the theme `primary`
role and degrade with the terminal.

[← Docs index](../../README.md#documentation)
