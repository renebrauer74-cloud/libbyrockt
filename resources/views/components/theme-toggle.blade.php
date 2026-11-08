@props(['label' => false])
{{-- Umschalter Hell/Dunkel – Logik in site.js bzw. admin.js ([data-theme-toggle]) --}}
<button {{ $attributes->merge(['class' => 'theme-toggle', 'type' => 'button']) }} data-theme-toggle aria-label="Dunkles Design" title="Dunkles Design">
    <x-icon name="moon" class="icon theme-icon-moon" />
    <x-icon name="sun" class="icon theme-icon-sun" />
@if ($label)
    <span class="theme-toggle-label" data-theme-label>Dunkles Design</span>
@endif
</button>
