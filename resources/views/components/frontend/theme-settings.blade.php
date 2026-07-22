<div
    class="floating-btn tw-z-[999] max-[767px]:tw-hidden tw-fixed tw-top-1/2 tw-right-0 -tw-translate-y-1/2 tw-rounded-s-xl">
    <button class="btn tw-rounded-s-xl tw-rounded-e-none tw-p-2.5 tw-bg-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
        aria-controls="offcanvasRight">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="tw-text-gray-900 tw-w-6 tw-h-6 loading">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
    </button>
    <a href="javascript:void(0)" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
        aria-controls="offcanvasRight">Theme Setting</a>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="tw-flex tw-px-5 tw-py-3 tw-border-b tw-border-gray-100 tw-justify-between tw-items-center">
        <h3 class="tw-text-xl tw-mb-0">Theme Settings</h3>
        <button class="btn" data-bs-dismiss="offcanvas" aria-label="Close">
            <x-svg.cross-icon width="20" height="20" />
        </button>
    </div>
    <div class="tw-px-5 tw-pb-6">
        <div id="landing-pages">
            <form action="{{ route('website.landingPage.update') }}" method="POST">
                @csrf
                @method('PUT')
                <h2 class="tw-text-base tw-mb-4">Home Page</h2>
                <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-6 tw-mb-6">
                    <label for="home01"
                        class="tw-cursor-pointer tw-shadow-md tw-rounded tw-overflow-hidden tw-border tw-border-gray-100">
                        <div class="tw-flex tw-gap-2 tw-px-2 tw-py-3 tw-items-center">
                            <input type="radio" value="1" name="current_theme" id="home01"
                                {{ $setting->landing_page == 1 ? 'checked' : '' }}>
                            <h4 class="tw-text-base tw-mb-0">Home 01</h4>
                        </div>
                    </label>
                    <label for="home02"
                        class="tw-cursor-pointer tw-shadow-md tw-rounded tw-overflow-hidden tw-border tw-border-gray-100">
                        <div class="tw-flex tw-gap-2 tw-px-2 tw-py-3 tw-items-center">
                            <input type="radio" value="2" name="current_theme" id="home02"
                                {{ $setting->landing_page == 2 ? 'checked' : '' }}>
                            <h4 class="tw-text-base tw-mb-0">Home 02</h4>
                        </div>
                    </label>
                    <label for="home03"
                        class="tw-cursor-pointer tw-shadow-md tw-rounded tw-overflow-hidden tw-border tw-border-gray-100">
                        <div class="tw-flex tw-gap-2 tw-px-2 tw-py-3 tw-items-center">
                            <input type="radio" value="3" name="current_theme" id="home03"
                                {{ $setting->landing_page == 3 ? 'checked' : '' }}>
                            <h4 class="tw-text-base tw-mb-0">Home 03</h4>
                        </div>
                    </label>
                </div>
            </form>

            <!-- color -->
            @php
                $sessionPrimaryColor = session('primaryColor');
                $sessionSecondaryColor = session('secondaryColor');
                $primaryColor = $sessionPrimaryColor ? $sessionPrimaryColor : $setting->frontend_primary_color;
                $secondaryColor = $sessionSecondaryColor ? $sessionSecondaryColor : $setting->frontend_secondary_color;
            @endphp

            <form action="{{ route('set.themeColor') }}" method="get" style="visibility: hidden"
                id="themeSwitcherForm">
                <input type="hidden" id="primaryColor" name="primaryColor" class="color-input"
                    value="{{ $primaryColor }}">
                <input type="hidden" id="secondaryColor" name="secondaryColor" class="color-input"
                    value="{{ $secondaryColor }}">
            </form>
        </div>
        <!-- PWA Script Start -->
        @if ($setting->pwa_enable)
            <!-- PWA Button Start -->
            <button class="btn tw-mt-6 !tw-p-0 bg-white d-none" id="installApp">
                <img src="{{ asset('pwa-btn.png') }}" alt="Install App" loading="lazy">
            </button>
        @endif
        <ul class="tw-list-none tw-space-y-2.5 tw-p-0 tw-mt-6">
            <li>
                <a class="tw-inline-flex tw-gap-2 tw-items-center tw-text-base" href="https://www.templatecookie.com/docs/jobpilot/introduction" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="tw-w-6 tw-h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25M9 16.5v.75m3-3v3M15 12v5.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span>Documentation</span>
                </a>
            </li>
            <li>
                <a class="tw-inline-flex tw-gap-2 tw-items-center tw-text-base" href="https://www.templatecookie.com/get-support" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="tw-w-6 tw-h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.712 4.33a9.027 9.027 0 0 1 1.652 1.306c.51.51.944 1.064 1.306 1.652M16.712 4.33l-3.448 4.138m3.448-4.138a9.014 9.014 0 0 0-9.424 0M19.67 7.288l-4.138 3.448m4.138-3.448a9.014 9.014 0 0 1 0 9.424m-4.138-5.976a3.736 3.736 0 0 0-.88-1.388 3.737 3.737 0 0 0-1.388-.88m2.268 2.268a3.765 3.765 0 0 1 0 2.528m-2.268-4.796a3.765 3.765 0 0 0-2.528 0m4.796 4.796c-.181.506-.475.982-.88 1.388a3.736 3.736 0 0 1-1.388.88m2.268-2.268 4.138 3.448m0 0a9.027 9.027 0 0 1-1.306 1.652c-.51.51-1.064.944-1.652 1.306m0 0-3.448-4.138m3.448 4.138a9.014 9.014 0 0 1-9.424 0m5.976-4.138a3.765 3.765 0 0 1-2.528 0m0 0a3.736 3.736 0 0 1-1.388-.88 3.737 3.737 0 0 1-.88-1.388m2.268 2.268L7.288 19.67m0 0a9.024 9.024 0 0 1-1.652-1.306 9.027 9.027 0 0 1-1.306-1.652m0 0 4.138-3.448M4.33 16.712a9.014 9.014 0 0 1 0-9.424m4.138 5.976a3.765 3.765 0 0 1 0-2.528m0 0c.181-.506.475-.982.88-1.388a3.736 3.736 0 0 1 1.388-.88m-2.268 2.268L4.33 7.288m6.406 1.18L7.288 4.33m0 0a9.024 9.024 0 0 0-1.652 1.306A9.025 9.025 0 0 0 4.33 7.288" />
                    </svg>
                    <span>Support</span>
                </a>
            </li>
            <li>
                <a class="tw-inline-flex tw-gap-2 tw-items-center tw-text-base" href="https://go.templatecookie.com/jobpilot-regular" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="tw-w-5 tw-h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                    <span>Purchase Now</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
    .floating-btn {
        box-shadow: 0px 2px 4px 0px #1C21260F;
    }
    .floating-btn button {
        border: 1px solid var(--gray-100);
    }
    .floating-btn a {
        visibility: hidden;
        opacity: 0;
        transform: translatex(-10px);
        transition: all 0.4s ease-in-out;
        white-space: nowrap;
        position: absolute;
        right: calc(100% + 10px);
        top: 5px;
        background: white;
        color: black;
        font-size: 14px;
        line-height: 20px;
        padding: 6px 10px;
        border: 1px solid var(--gray-100);
        border-radius: 8px;
        z-index: 999;
        box-shadow: 0px 2px 4px 0px #1C21260F;
    }

    .floating-btn:hover a {
        transform: translatex(0);
        visibility: visible;
        opacity: 1;
    }

    .wave-animation {
        animation-name: wave-animation !important;
        animation-duration: 3.5s !important;
        animation-iteration-count: infinite !important;
        transform-origin: 70% 70% !important;
    }

    .wave-animation:hover {
        animation-name: unset !important
    }

    @keyframes wave-animation {
        0% {
            transform: rotate(0)
        }

        10% {
            transform: rotate(12deg)
        }

        20% {
            transform: rotate(-6deg)
        }

        30% {
            transform: rotate(12deg)
        }

        40% {
            transform: rotate(-2deg)
        }

        50% {
            transform: rotate(8deg)
        }

        60% {
            transform: rotate(0)
        }

        100% {
            transform: rotate(0)
        }
    }
    @keyframes rotation {
        from {
            -webkit-transform: rotate(0deg);
        }

        to {
            -webkit-transform: rotate(359deg);
        }
    }

    .loading {
        animation: rotation 5s infinite linear;
    }

    /*=== Media Query ===*/
    .panel-group:last-child {
        margin-bottom: 0;
    }

    .panel-group .panel-title {
        position: relative;
        margin-bottom: 12px;
        z-index: 0;
    }

    .panel-group .panel-title .title {
        display: inline-block;
        padding-right: 10px;
        color: #333;
        font-size: 14px;
        font-weight: 700;
        background: #fff;
        padding-bottom: 0;
        margin-bottom: 0;
        border-bottom: none;
        margin-top: 0;
    }

    .panel-group .panel-title .title::after {
        position: absolute;
        content: "";
        left: 0;
        top: 10px;
        height: 1px;
        width: 100%;
        background: #ebebeb;
        z-index: -1;
    }

    .panel-group .color-skin {
        display: flex;
        flex-wrap: wrap;
        margin: -7px -7px 0px;
        padding: 0;
        list-style: none;
    }

    .panel-group .color-skin .color-item {
        display: inline-block;
        position: relative;
        flex: 1 0 calc(15% - 14px);
        margin: 7px;
        border-radius: 2px;
        cursor: pointer;
    }

    .panel-group .color-skin .color-item::before {
        content: "";
        display: block;
        padding-bottom: 100%;
    }

    .panel-group .color-skin .color-item::after {
        position: absolute;
        content: "";
        left: 50%;
        top: calc(50% - 5px);
        height: 7px;
        width: 12px;
        border: 2px solid #fff;
        border-top: none;
        border-right: none;
        opacity: 0;
        visibility: hidden;
        transform: translateX(-50%) rotate(-45deg);
    }

    .panel-group .color-skin .color-item.active::after {
        opacity: 1;
        visibility: visible;
    }

    .buttons button:focus {
        box-shadow: none;
        outline: none;
    }

    .buttons {
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>
<!-- Click Connector Widget -->
{{-- <script>
    function loadCCWidget() {
        (new window["click-connector-widget"]).mount({})
    }

    function loadCCScript() {
        var t = document.createElement("script");
        t.id = "cc-widget-script", t.setAttribute("data-widget-id", "83c890-101f0"), t.type = "text/javascript", t
            .defer = !0, t.addEventListener("load", (function(t) {
                loadCCWidget()
            })), t.src = "https://widget.clickconnector.app/widget.js", document.getElementsByTagName("head")[0]
            .appendChild(t)
    }
    loadCCScript();
</script> --}}
<!-- End Click Connector Widget -->

<!-- Tidio Chat Widget    -->
<script src="//code.tidio.co/nupcnf4jzm9la8auahqytgbc2iepfewv.js" async></script>
<!-- End Tidio Chat Widget -->

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const radioButtons = document.querySelectorAll('input[type="radio"][name="current_theme"]');
        radioButtons.forEach(function(radio) {
            radio.addEventListener("click", function() {
                this.closest("form").submit();
            });
        });
    });
</script>
<script>
    const colorVariables = [{
            class: 'primary-color',
            id: 'primaryColor',
            variable: '--bs-primary-500',
            title: "Primary color",
            colors: [
                "#864AF9",
                "#FF4B00",
                "#F30CDC",
                "#226EDD",
                "#27D8CF",
                "#1CE342",
                "#8DE31C",
                "#F2B60D",
                "#F4610B",
                "#18191C",
                "#2DB24A",
                "#497174"
            ]
        },
        {
            class: 'secondary-color',
            id: 'secondaryColor',
            variable: '--bs-secondary-500',
            title: "Secondary color",
            colors: [
                "#FF5C5C",
                "#FF944D",
                "#FFD91A",
                "#8FCC14",
                "#2DB24A",
                "#0BBAE6",
                "#1777E5",
                "#3312FF",
                "#8A43FF",
                "#E543FF",
                "#132238",
                "#697484",
            ]
        },
    ]
    const themeSwitcher = false;

    //Theme Switcher Panel
    const themePanelInit = () => {
        const dataTheme = $('body').attr('data-theme');
        const defaultActive = dataTheme ? dataTheme : 'light';

        $('#landing-pages').append(`
                <div class="">
                    ${colorVariables.map((item) => `
                                <div class="panel-group">
                                    <div class="panel-title">
                                        <h6 class="title">${item.title}</h6>
                                    </div>
                                    <ul class="color-skin">
                                        ${item.colors.map((color) => `<li data-color="${color}" class="color-item ${item.class}"></li>`
                                        ).join("")}
                                    </ul>
                                </div>`)
                    .join("")}
                </div>
        `)

        // window load set active color active class
        colorVariables.forEach((color) => {
            let colorInput = document.querySelector(`#${color.id}`);
            let activeColorItem = document.querySelector(
                `.${color.class}[data-color="${colorInput.value}"]`);
            if (activeColorItem) {
                activeColorItem.classList.add('active')
            }
        })
    }

    // Detect click from all colors and set the specefic color on form input/body
    const changeThemeColor = () => {
        const root = document.documentElement;
        // Detect Click
        colorVariables.forEach((color) => {
            const colorSets = document.querySelectorAll(`.${color.class}`);
            console.log(colorSets);
            // loop through all colors
            Array.from(colorSets).forEach((item) => {
                item.style.backgroundColor = item.dataset.color;

                item.addEventListener('click', (e) => {
                    // remove active class from others;
                    removeClassFromSiblings(colorSets);

                    // set active color
                    const clickedItem = e.target;
                    clickedItem.classList.add('active');
                    const clickedItemValue = clickedItem.dataset.color;

                    // set variable color
                    root.style.setProperty(color.variable, clickedItemValue);
                    // localStorage.setItem(color.variable, clickedItemValue)
                    setThemeColor(color.id, clickedItemValue)
                });
            })
        });

        // remove a specefic class from other
        function removeClassFromSiblings(colorSets) {
            Array.from(colorSets).forEach((item) => {
                item.classList.remove('active');
            })
        }
    }

    // set theme color in form input and submit the form
    const setThemeColor = (variable, color) => {
        $(`#themeSwitcherForm #${variable}`).val(color);
        $('#themeSwitcherForm').submit();
    }

    if (themeSwitcher) {
        // client dark lite changer
        const toggleSwitch = document.querySelector(".toggle-button");
        const documentBody = document.body;

        toggleSwitch.addEventListener("change", function(e) {
            const mode = e.target.checked === true ? 'dark' : 'light';
            documentBody.setAttribute("data-theme", mode);
        });

        window.addEventListener('load', () => {
            const mode = localStorage.getItem('color_mode') ?? 'light';
            document.body.setAttribute("data-theme", mode);
        })

        const observer = new MutationObserver(function() {
            const mode = documentBody.getAttribute('data-theme');

            localStorage.setItem('color_mode', mode);
            toggleSwitch.checked = mode === 'dark' ? true : false;
        });

        observer.observe(documentBody, {
            attributeFilter: ['data-theme']
        });
    }

    // Initialize the color panel
    $(function() {
        themePanelInit();
        // on click change variable color
        changeThemeColor();
    })
</script>
<!-- PWA Button End -->
<script src="{{ asset('/sw.js') }}"></script>
<script>
    if (!navigator.serviceWorker) {
        navigator.serviceWorker.register("/sw.js").then(function(reg) {
            console.log("Service worker has been registered for scope: " + reg);
        });
    }

    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        $('#installApp').removeClass('d-none');
        deferredPrompt = e;
    });

    const installApp = document.getElementById('installApp');
    installApp.addEventListener('click', async () => {
        if (deferredPrompt !== null) {
            deferredPrompt.prompt();
            const {
                outcome
            } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                deferredPrompt = null;
            }
        }
    });
</script>
