{{-- Google Tag Manager - Noscript fallback. Include immediately after opening <body>. --}}
@php $gtmId = optional($seo_settings)->gtm_container_id; @endphp
@if($gtmId && preg_match('/^GTM-[A-Z0-9]+$/', $gtmId))
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif
