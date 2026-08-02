<title>{{$title}} - {{setting("{$lng}.general.title")}}</title>
<meta name="description" content="{{$brief}}">
<meta name="author" content="{{$author}}">
<meta name="keyword" content="{{$keyword}}">

@if( setting("{$lng}.general.google_site_verification") )
    <meta name="google-site-verification" content="{{setting("{$lng}.general.google_site_verification")}}">
@endif 

<link rel="canonical" href="{{url()->current()}}">

@foreach(config('app.locales') as $lang)
    @if($lng === $lang)
        <link rel="alternate" href="{{url()->current()}}" hreflang="{{$lang . '-jo'}}">
    @else
        <link rel="alternate" href="{{ str_replace($lng,$lang,url()->current());}}" hreflang="{{$lang . '-jo'}}">
    @endif
@endforeach

<meta property="og:title" content="{{$title}}">
<meta property="og:description" content="{{$brief}}">
<meta property="og:image" content="{{$image}}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="{{$ogType}}">
<meta property="og:site_name" content="{!!setting("{$lng}.general.title")!!}">

<meta name="twitter:title" content="{{$title}}">
<meta name="twitter:description" content="{{$brief}}">
<meta name="twitter:site" content="{{url()->current()}}">
<meta name="twitter:card" content="{{$twitterType}}">
<meta name="twitter:image" content="{{$image}}">


@if (!App::environment('prod') || $isPreview)
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
    <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
@else
    <meta name="robots" content="{{ $robots }}">
    <meta name="googlebot" content="{{ $robots }}">
@endif

<script type="application/ld+json">
{
  {{-- Escaped: Laravel 11+ added an @context Blade directive, so a bare
       "@context" here is compiled as Blade and breaks the template. --}}
  "@@context": "https://schema.org",
  "@type": "WebSite",
  "name": "{{setting("{$lng}.general.title")}}",
  "url": "{{request()->getSchemeAndHttpHost()}}"
}
</script>
