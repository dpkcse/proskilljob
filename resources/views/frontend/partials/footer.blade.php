<div class="rt-site-footer bg-gray-900 dark-footer">
    <div class="footer-top  bg-gray-900">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-5 col-sm-6 rt-single-widget ">
                    <a href="#" class="footer-logo">
                        <img src="{{ $setting->light_logo_url }}" alt="logo" loading="lazy">
                    </a>
                    <address>
                        <div class="body-font-2 text-gray-500">
                            @if ($cms_setting?->footer_phone_no)
                                <div class="body-font-2 text-gray-500">
                                    <span>{{ __('call_now') }}:</span>
                                    <a href="tel:{{ $cms_setting?->footer_phone_no }}" class="text-gray-10">
                                        {{ $cms_setting?->footer_phone_no }}</a>
                                </div>
                            @endif
                            <div class="max-312 body-font-4 mt-2 text-gray-500">
                                {{ __('footer_description') }}
                            </div>
                                <div class="body-font-4 mt-3 text-gray-500">
                                    <div class="d-flex align-items-start">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2 mt-1">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#767E94"/>
                                        </svg>
                                        <div>
                                            <span class="text-gray-10 fw-medium">{{ __('address') }}: {{ __('footer_address') }}</span>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </address>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 rt-single-widget ">
                    <h2 class="footer-title">{{ __('company') }}</h2>
                    <ul class="rt-usefulllinks2">
                        <li><a href="{{ route('website.about') }}">{{ __('about') }}</a></li>
                        <li><a href="{{ route('website.contact') }}">{{ __('contact') }}</a></li>
                        @guest
                            <li><a href="{{ route('website.plan') }}">{{ __('pricing') }}</a></li>
                        @endguest
                        @if (auth('user')->check() && authUser()->role != 'candidate')
                            <li><a href="{{ route('website.plan') }}">{{ __('pricing') }}</a></li>
                        @endif
                        @foreach ($custom_pages->where('show_footer', 1)->where('footer_column_position', 1) as $page)
                        <li><a href="{{ route('showCustomPage', $page->slug) }}">{{ $page->title }}</a></li>
                        @endforeach
                        <li><a href="{{ route('website.posts') }}">{{ __('blog') }}</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 rt-single-widget ">
                    <h2 class="footer-title">{{ __('candidate') }}</h2>
                    <ul class="rt-usefulllinks2">
                        <li><a href="{{ route('website.job') }}">{{ __('browse_jobs') }}</a></li>
                        @if (!auth('user')->check() || authUser()->role != 'candidate')
                            <li><a href="{{ route('website.candidate') }}">{{ __('browse_candidates') }}</a></li>
                        @endif
                        <li><a href="{{ route('candidate.dashboard') }}">{{ __('candidate_dashboard') }}</a></li>
                        <li><a href="{{ route('candidate.bookmark') }}">{{ __('saved_jobs') }}</a></li>
                        @if(moduleActive('CandidatePlan'))
                        @if($hasActiveCandidatePlan)
                        <li><a href="{{ route('candidate.plan') }}">{{ __('candidate_plan') }}</a></li>
                        @endif
                        @endif
                        @foreach ($custom_pages->where('show_footer', 1)->where('footer_column_position', 2) as $page)
                        <li><a href="{{ route('showCustomPage', $page->slug) }}">{{ $page->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 rt-single-widget ">
                    <h2 class="footer-title">{{ __('employer') }}</h2>
                    <ul class="rt-usefulllinks2">
                        <li><a href="{{ route('company.job.create') }}">{{ __('post_a_job') }}</a></li>
                        @if (!auth('user')->check() || authUser()->role != 'company')
                            <li><a href="{{ route('website.company') }}">{{ __('browse_employers') }}</a></li>
                        @endif
                        <li><a href="{{ route('company.dashboard') }}">{{ __('employers_dashboard') }}</a></li>
                        <li><a href="{{ route('company.myjob') }}">{{ __('applications') }}</a></li>
                        @foreach ($custom_pages->where('show_footer', 1)->where('footer_column_position', 3) as $page)
                        <li><a href="{{ route('showCustomPage', $page->slug) }}">{{ $page->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 rt-single-widget ">
                    <h2 class="footer-title">{{ __('support') }}</h2>
                    <ul class="rt-usefulllinks2">
                        <li><a href="{{ route('website.faq') }}">{{ __('faq') }}</a></li>
                        <li><a href="{{ route('website.privacyPolicy') }}">{{ __('privacy_policy') }}</a></li>
                        <li><a href="{{ route('website.termsCondition') }}">{{ __('terms_condition') }}</a></li>
                        <li><a href="{{ route('website.refundPolicy') }}">{{ __('refund_policy') }}</a></li>
                        @foreach ($custom_pages->where('show_footer', 1)->where('footer_column_position', 4) as $page)
                        <li><a href="{{ route('showCustomPage', $page->slug) }}">{{ $page->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container -->
    </div><!-- /.footer-top -->
    <div class="footer-bottom bg-gray-900">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 text-center text-lg-start f-size-14 text-gray-500">
                    <x-website.footer-copyright />
                </div><!-- /.col-lg-6 -->
                <div class="col-lg-6 text-center text-lg-end">
                    <ul class="footer-social-links">
                        @if ($cms_setting?->footer_facebook_link)
                            <li>
                                <a href="{{ $cms_setting->footer_facebook_link }}" title="facebook">
                                    <svg width="20" height="20" viewBox="0 0 10 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M8.17403 3.32083H9.99986V0.140833C9.68486 0.0975 8.60153 0 7.33986 0C4.70736 0 2.90402 1.65583 2.90402 4.69917V7.5H-0.000976562V11.055H2.90402V20H6.46569V11.0558H9.25319L9.69569 7.50083H6.46486V5.05167C6.46569 4.02417 6.74236 3.32083 8.17403 3.32083Z"
                                            fill="#767E94" />
                                    </svg>
                                </a>
                            </li>
                        @endif
                        @if ($cms_setting?->footer_instagram_link)
                            <li>
                                <a href="{{ $cms_setting->footer_instagram_link }}" title="Instagram">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip01)">
                                        <path
                                            d="M19.9804 5.88005C19.9336 4.81738 19.7617 4.0868 19.5156 3.45374C19.2616 2.78176 18.8709 2.18014 18.359 1.68002C17.8589 1.1721 17.2533 0.777435 16.5891 0.527447C15.9524 0.281274 15.2256 0.109427 14.163 0.0625732C13.0923 0.0117516 12.7525 0 10.0371 0C7.32172 0 6.98185 0.0117516 5.9152 0.0586052C4.85253 0.105459 4.12195 0.277459 3.48904 0.523479C2.81692 0.777435 2.2153 1.16814 1.71517 1.68002C1.20726 2.18014 0.812742 2.78573 0.562602 3.44992C0.31643 4.0868 0.144583 4.81341 0.0977294 5.87609C0.0469078 6.9467 0.0351562 7.28658 0.0351562 10.002C0.0351562 12.7173 0.0469078 13.0572 0.0937614 14.1239C0.140615 15.1865 0.312615 15.9171 0.558787 16.5502C0.812742 17.2221 1.20726 17.8238 1.71517 18.3239C2.2153 18.8318 2.82088 19.2265 3.48507 19.4765C4.12195 19.7226 4.84856 19.8945 5.91139 19.9413C6.97788 19.9883 7.31791 19.9999 10.0333 19.9999C12.7486 19.9999 13.0885 19.9883 14.1552 19.9413C15.2178 19.8945 15.9484 19.7226 16.5813 19.4765C17.9254 18.9568 18.9881 17.8941 19.5078 16.5502C19.7538 15.9133 19.9258 15.1865 19.9726 14.1239C20.0195 13.0572 20.0312 12.7173 20.0312 10.002C20.0312 7.28658 20.0273 6.9467 19.9804 5.88005ZM18.1794 14.0457C18.1364 15.0225 17.9723 15.5499 17.8355 15.9015C17.4995 16.7728 16.808 17.4643 15.9367 17.8004C15.585 17.9372 15.0538 18.1012 14.0808 18.1441C13.026 18.1911 12.7096 18.2027 10.0411 18.2027C7.37255 18.2027 7.0522 18.1911 6.00113 18.1441C5.02437 18.1012 4.49693 17.9372 4.1453 17.8004C3.71171 17.6402 3.31704 17.3862 2.9967 17.0541C2.6646 16.7298 2.41065 16.3391 2.2504 15.9055C2.11365 15.5539 1.94959 15.0225 1.9067 14.0497C1.8597 12.9948 1.8481 12.6783 1.8481 10.0097C1.8481 7.34122 1.8597 7.02087 1.9067 5.96995C1.94959 4.99319 2.11365 4.46575 2.2504 4.11412C2.41065 3.68038 2.6646 3.28586 3.00067 2.96536C3.32483 2.63327 3.71553 2.37931 4.14927 2.21921C4.5009 2.08247 5.03231 1.9184 6.00509 1.87537C7.05999 1.82851 7.37651 1.81676 10.0449 1.81676C12.7174 1.81676 13.0337 1.82851 14.0848 1.87537C15.0616 1.9184 15.589 2.08247 15.9406 2.21921C16.3742 2.37931 16.7689 2.63327 17.0892 2.96536C17.4213 3.28967 17.6753 3.68038 17.8355 4.11412C17.9723 4.46575 18.1364 4.99701 18.1794 5.96995C18.2262 7.02484 18.238 7.34122 18.238 10.0097C18.238 12.6783 18.2262 12.9908 18.1794 14.0457Z"
                                            fill="#767E94" />
                                        <path
                                            d="M10.0371 4.86401C7.20074 4.86401 4.89941 7.16518 4.89941 10.0017C4.89941 12.8383 7.20074 15.1395 10.0371 15.1395C12.8737 15.1395 15.1749 12.8383 15.1749 10.0017C15.1749 7.16518 12.8737 4.86401 10.0371 4.86401ZM10.0371 13.3344C8.19702 13.3344 6.70442 11.842 6.70442 10.0017C6.70442 8.16147 8.19702 6.66902 10.0371 6.66902C11.8774 6.66902 13.3698 8.16147 13.3698 10.0017C13.3698 11.842 11.8774 13.3344 10.0371 13.3344Z"
                                            fill="#767E94" />
                                        <path
                                            d="M16.5777 4.6611C16.5777 5.32346 16.0407 5.86052 15.3781 5.86052C14.7158 5.86052 14.1787 5.32346 14.1787 4.6611C14.1787 3.99858 14.7158 3.46167 15.3781 3.46167C16.0407 3.46167 16.5777 3.99858 16.5777 4.6611Z"
                                            fill="#767E94" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip033">
                                            <rect width="20" height="20" fill="transparent" />
                                        </clipPath>
                                    </defs>
                                </svg>
                                </a>
                            </li>
                        @endif
                        @if ($cms_setting?->footer_youtube_link)
                            <li>
                                <a href="{{ $cms_setting->footer_youtube_link }}" title="YouTube">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip3)">
                                        <path
                                            d="M19.5879 5.19872C19.3574 4.34194 18.6819 3.66659 17.8252 3.43588C16.2602 3.00757 9.99981 3.00757 9.99981 3.00757C9.99981 3.00757 3.73961 3.00757 2.17452 3.41955C1.33438 3.65011 0.642392 4.3421 0.411833 5.19872C0 6.76366 0 10.0092 0 10.0092C0 10.0092 0 13.271 0.411833 14.8197C0.642545 15.6763 1.3179 16.3518 2.17467 16.5825C3.75609 17.0108 9.99996 17.0108 9.99996 17.0108C9.99996 17.0108 16.2602 17.0108 17.8252 16.5988C18.682 16.3683 19.3574 15.6928 19.5881 14.8361C19.9999 13.271 19.9999 10.0257 19.9999 10.0257C19.9999 10.0257 20.0164 6.76366 19.5879 5.19872Z"
                                            fill="#767E94" />
                                        <path class="facebook"
                                            d="M8.00635 13.0077L13.2122 10.0093L8.00635 7.01099V13.0077Z"
                                            fill="black" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip02">
                                            <rect width="20" height="20" fill="transparent" />
                                        </clipPath>
                                    </defs>
                                </svg>
                                </a>
                            </li>
                        @endif
                        @if ($cms_setting?->footer_twitter_link)
                            <li>
                                <a href="{{ $cms_setting->footer_twitter_link }}" title="Twitter">
                                    <x-svg.new-twitter-icon width="20" height="20" fill="#727279"/>
                                </a>
                            </li>
                        @endif
                        @if ($cms_setting?->footer_linkedin_link)
                            <li>
                                <a href="{{ $cms_setting->footer_linkedin_link }}" title="LinkedIn">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 2.66051C0 2.01699 0.225232 1.48611 0.675676 1.06784C1.12612 0.649561 1.71172 0.44043 2.43243 0.44043C3.14029 0.44043 3.71299 0.646337 4.15058 1.05819C4.60102 1.4829 4.82625 2.0363 4.82625 2.71842C4.82625 3.33618 4.60747 3.85097 4.16988 4.26282C3.71944 4.68753 3.12741 4.89989 2.39382 4.89989H2.37452C1.66666 4.89989 1.09396 4.68753 0.656371 4.26282C0.218784 3.83811 0 3.304 0 2.66051ZM0.250965 19.5524V6.65664H4.53668V19.5524H0.250965ZM6.9112 19.5524H11.1969V12.3516C11.1969 11.9012 11.2484 11.5537 11.3514 11.3092C11.5315 10.8716 11.805 10.5015 12.1718 10.1991C12.5386 9.89666 12.9987 9.74545 13.5521 9.74545C14.9936 9.74545 15.7143 10.7171 15.7143 12.6605V19.5524H20V12.1586C20 10.2538 19.5496 8.80915 18.6486 7.8246C17.7477 6.84004 16.5573 6.34776 15.0772 6.34776C13.417 6.34776 12.1236 7.06205 11.1969 8.49062V8.52923H11.1776L11.1969 8.49062V6.65664H6.9112C6.93693 7.06848 6.94981 8.34904 6.94981 10.4983C6.94981 12.6476 6.93693 15.6656 6.9112 19.5524Z" fill="#727279"/>
                                    </svg>
                                </a>
                            </li>
                        @endif
                        @if ($cms_setting?->footer_pinterest_link)
                            <li>
                                <a href="{{ $cms_setting->footer_pinterest_link }}" title="Pinterest">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 0C4.477 0 0 4.477 0 10c0 4.237 2.636 7.855 6.356 9.312-.088-.791-.167-2.006.035-2.868.181-.78 1.172-4.97 1.172-4.97s-.299-.599-.299-1.484c0-1.388.805-2.425 1.808-2.425.853 0 1.264.64 1.264 1.407 0 .858-.546 2.14-.828 3.33-.236.995.499 1.807 1.479 1.807 1.775 0 3.141-1.872 3.141-4.57 0-2.39-1.72-4.04-4.177-4.04-2.845 0-4.516 2.135-4.516 4.34 0 .859.331 1.781.745 2.281a.3.3 0 01.069.288l-.278 1.133c-.044.183-.145.223-.334.135-1.249-.581-2.03-2.407-2.03-3.874 0-3.154 2.292-6.052 6.608-6.052 3.469 0 6.165 2.473 6.165 5.776 0 3.447-2.173 6.22-5.19 6.22-1.013 0-1.965-.527-2.292-1.155l-.623 2.378c-.226.869-.835 1.958-1.244 2.621.937.29 1.931.446 2.962.446 5.523 0 10-4.477 10-10S15.523 0 10 0z" fill="#727279"/>
                                    </svg>
                                </a>
                            </li>
                        @endif
                        @if ($cms_setting?->footer_tiktok_link)
                            <li>
                                <a href="{{ $cms_setting->footer_tiktok_link }}" title="TikTok">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19.589 6.686a4.793 4.793 0 01-3.77-4.245V2h-3.445v13.672a2.896 2.896 0 01-5.201 1.743l-.002-.001.002.001a2.895 2.895 0 01-3.184-4.535 2.896 2.896 0 015.201 1.743V7.1H6.374a6.3 6.3 0 00-1.1 12.489 6.3 6.3 0 001.1-12.489h3.445v3.445a6.3 6.3 0 1012.489-1.1 6.3 6.3 0 00-1.1-12.489z" fill="#727279"/>
                                    </svg>
                                </a>
                            </li>
                        @endif
                        @if ($cms_setting?->footer_whatsapp_link)
                            <li>
                                <a href="{{ $cms_setting->footer_whatsapp_link }}" title="WhatsApp">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488" fill="#727279"/>
                                    </svg>
                                </a>
                            </li>
                        @endif
                    </ul>
                </div><!-- /.col-lg-6 -->
            </div><!-- /.row -->
        </div><!-- /.container -->
    </div><!-- /.footer-bottom -->
</div><!-- /.rt-site-footer -->
