{{--
    dompdf doesn't execute compiled Tailwind (no Vite asset pipeline in a
    non-HTTP render), so PDF templates use this plain CSS instead of the
    Tailwind utility classes the on-screen print views use. Kept minimal
    and shared across all *.pdf.blade.php views to avoid repeating it.
--}}
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
    table { width: 100%; border-collapse: collapse; }
    .border { border: 1px solid #1f2937; }
    .border-b { border-bottom: 1px solid #1f2937; }
    .border-r { border-right: 1px solid #1f2937; }
    .border-t { border-top: 1px solid #1f2937; }
    .row-border { border-bottom: 1px solid #d1d5db; }
    .p { padding: 6px; }
    .p-sm { padding: 3px 6px; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .uppercase { text-transform: uppercase; }
    .text-sm { font-size: 10px; }
    .text-lg { font-size: 15px; }
    .mt { margin-top: 8px; }
    .mb { margin-bottom: 8px; }
    .muted { color: #4b5563; }
</style>
