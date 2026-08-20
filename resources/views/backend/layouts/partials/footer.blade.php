<footer class="main-footer d-flex justify-content-between">
    <div>
        <strong>&copy; {{ date('Y') }} <a href="{{ config('app.url') }}">{{ config('brand.company_name') }}</a>.</strong>
        {{ __('all_rights_reserved') }}. {{ config('brand.product_attribution') }}
    </div>
    <div class="float-right d-none d-sm-inline-block pr-5">
        <b>{{ __('version') }}</b> {{ config('app.version') }}
    </div>
</footer>
