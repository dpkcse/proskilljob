@extends('frontend.layouts.app')

@section('description'){{ strip_tags($post->description) }}@endsection
@section('og:image'){{ asset($post->image) }}@endsection
@section('title'){{ $post->title }}@endsection

@section('main')
    <div class="breadcrumbs breadcrumbs-height">
        <div class="container">
            <div class="breadcrumb-menu">
                <h6 class="f-size-18 m-0">{{ __('blog_deatils') }}</h6>
                <ul>
                    <li><a href="{{ route('website.home') }}">{{ __('home') }}</a></li>
                    <li>/</li>
                    <li>{{ __('blog_deatils') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container">
        <!-- google adsense area  -->
        @if (advertisementCode('blog_detailpage_inside_blog'))
            <div style="margin-top:50px;">
                {!! advertisementCode('blog_detailpage_inside_blog') !!}
            </div>
        @endif
        <!-- google adsense area end -->
    </div>

    <div class="blog-content-area rt-pt-50 rt-mb-100 rt-mb-md-20">
        <div class="container">
            <div class="single-blog-page">
                <article class="single-blog-post blog-post hover-shadow:none">
                    <h4 class="rt-mb-24">{{ $post->title }}</h4>
                    <div class="entry-meta tw-flex-wrap tw-gap-3 align-items-center">
                        <a class="author-img-link d-flex align-items-center" href="#">
                            <img src="{{ asset($post->author->image_url) }}" alt="Author"  class="tw-w-[68px] tw-h-[68px] tw-rounded-md object-fit-contain">
                            <span class="body-font-3 text-gray-700"> {{ $post->author->name }}</span>
                        </a>
                        <a class="date" href="#">
                            <i class="ph-calendar-blank"></i>
                            {{ date('M d, Y', strtotime($post->created_at)) }}
                        </a>
                        @if (count($post->comments) != 0)
                            <a class="comment" href="{{ route('website.post', $post->slug) }}#comments">
                                <i class="ph-chat-circle-dots"></i>
                                {{ $post->commentsCount() }} {{ __('comments') }}
                            </a>
                        @endif
                    </div>
                    <div >
                        <img src="{{ url($post->image) }}" alt="post" >
                    </div>
                    <br>
                    <h6 class="rt-mb-24">
                        {{ $post->short_description }}
                    </h6>
                    <div class="body-font-3 text-gray-600">
                        {!! $post->description !!}
                    </div>
                    <br>
                    <div class="rt-spacer-60"></div>
                    <div class="tw-share-area  tw-pt-6 tw-pb-8">
                        <h2 class="tw-text-[#18191C] tw-text-lg tw-font-medium tw-mb-2">{{ __('share_this_job') }}:
                        </h2>

                        <div class="input-group mb-3">
                        <input type="text" class="form-control" value="{{ url()->current() }}" aria-label="Recipient's username" aria-describedby="basic-addon2">
                        <span class="tw-text-primary-500 hover:tw-bg-primary-500 tw-cursor-pointer hover:tw-text-white tw-flex tw-gap-1.5 tw-items-center tw-text-base tw-font-medium tw-bg-[#E7F0FA] tw-px-4 tw-py-2 tw-rounded-[4px]"
                                                            onclick="copyUrl('{{ url()->current() }}')" id="basic-addon2"><x-svg.link-sample-icon /></span>
                        </div>
                        <ul class="tw-list-none tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-p-0 tw-m-0 tw-mb-6">
                            <li>
                                <a href="javascript:void(0);" onclick="openPopUp('{{ socialMediaShareLinks(url()->current(), 'facebook') }}')"
                                    class="tw-inline-flex tw-bg-[#E7F0FA] tw-text-[#0A65CC] hover:tw-bg-[#0A65CC] hover:tw-text-white tw-rounded-[4px] tw-p-2.5">
                                    <x-svg.new-facebook-icon />
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" onclick="openPopUp('{{ socialMediaShareLinks(url()->current(), 'pinterest') }}')"
                                    class="tw-inline-flex tw-bg-[#E7F0FA] tw-text-[#0A65CC] hover:tw-bg-[#0A65CC] hover:tw-text-white tw-rounded-[4px] tw-p-2.5">
                                    <x-svg.pinterest-icon/>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" onclick="openPopUp('{{ socialMediaShareLinks(url()->current(), 'twitter') }}')"
                                    class="tw-inline-flex tw-bg-[#E7F0FA] tw-text-primary-500 hover:tw-bg-primary-500 hover:tw-text-white tw-rounded-[4px] tw-p-2.5">
                                    <x-svg.new-twitter-icon />
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" onclick="openPopUp('{{ socialMediaShareLinks(url()->current(), 'whatsapp') }}')"
                                    class="tw-inline-flex tw-bg-[#E7F0FA] tw-text-primary-500 hover:tw-bg-primary-500 hover:tw-text-white tw-rounded-[4px] tw-p-2.5">
                                    <x-svg.whatsapp-icon />
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" onclick="openPopUp('{{ socialMediaShareLinks(url()->current(), 'linkedin')}}')"
                                    class="tw-inline-flex tw-bg-[#E7F0FA] tw-text-primary-500 hover:tw-bg-primary-500 hover:tw-text-white tw-rounded-[4px] tw-p-2.5">
                                   <x-svg.linkedin-icon/>
                                </a>
                            </li>

                            <li>
                                <a href="javascript:void(0);" onclick="openPopUp('{{ socialMediaShareLinks(url()->current(), 'mail')}}')"
                                    class="tw-inline-flex tw-bg-[#E7F0FA] tw-text-primary-500 hover:tw-bg-primary-500 hover:tw-text-white tw-rounded-[4px] tw-p-2.5">
                                   <x-svg.mail-icon/>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" onclick="openPopUp('{{ socialMediaShareLinks(url()->current(), 'telegram')}}')"
                                    class="tw-inline-flex tw-bg-[#E7F0FA] tw-text-primary-500 hover:tw-bg-primary-500 hover:tw-text-white tw-rounded-[4px] tw-p-2.5">
                                   <x-svg.telegram-icon/>
                                </a>
                            </li>

                            <li>
                                <a href="javascript:void(0);" onclick="openPopUp('{{ socialMediaShareLinks(url()->current(), 'skype')}}')"
                                    class="tw-inline-flex tw-bg-[#E7F0FA] tw-text-primary-500 hover:tw-bg-primary-500 hover:tw-text-white tw-rounded-[4px] tw-p-2.5">
                                   <x-svg.skype-icon/>
                                </a>
                            </li>
                        </ul>
                    </div>
                </article>

                <div class="comments-elemenst rt-pt-100 rt-pt-md-50" id="comments">
                    <h6 class="rt-mb-32">{{ __('write_a_comment') }}</h6>
                    <form action="{{ route('website.comment', $post->slug) }}" class="rt-mb-50" method="post">
                        @csrf
                        <textarea rows="4" name="body" placeholder="{{ __('share_your_thoughts_on_this_post') }}?" class="rt-mb-15"></textarea>
                        @auth()
                            <button type="submit" class="btn btn-primary">{{ __('post_a_comment') }}</button>
                        @else
                            <button type="submit"
                                class="btn btn-primary login_required">{{ __('post_a_comment') }}</button>
                        @endauth
                    </form>
                    <ul class="comments-list rt-list">
                        @forelse ($post->comments as $comment)
                            <li class="single-comments">
                                <div class="rt-single-icon-box rt-mb-15">
                                    <div class="icon-thumb rt-mr-16">
                                        <div class="user-img">
                                            <img src="{{ url($comment->user->image) }}" alt="Image" class="!tw-object-cover !tw-w-12 !tw-h-12 !tw-rounded-full">
                                        </div>
                                    </div>
                                    <div class="iconbox-content body-font-3 text-gray-700">
                                        <a class="user-name ft-wt-5 rt-mb-4 text-gray-900 hover:text-primary-500"
                                            href="#">{{ $comment->user->name }}</a>
                                        <span
                                            class="d-block body-font-4 text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="body-font-3 text-gray-700">
                                    {!! nl2br($comment->body) !!}
                                </div>
                                <div class="body-font-4 mt-3">
                                    <button id="replies-{{ $comment->id }}" data-id="{{ $comment->id }}"
                                        class="btn btn-sm reply tw-p-0 tw-inline-flex tw-gap-2 tw-items-center tw-text-[#0A65CC]"
                                        onclick="showHideForm('reply-{{ $comment->id }}')">
                                        <span>
                                            <x-svg.reply-icon />
                                        </span>
                                        <span>{{ __('reply') }}</span>
                                    </button>
                                    <form id="reply-{{ $comment->id }}"
                                        action="{{ route('website.comment', $post->slug) }}" class="rt-mb-50 d-none"
                                        method="post">
                                        @csrf
                                        <div class="tw-flex tw-gap-4 tw-justify-between tw-items-center tw-pt-4">
                                            <div class="tw-w-[50px] tw-h-[50px] tw-overflow-hidden">
                                                <img src="{{ url($comment->user->image) }}" alt="user" class="tw-w-full tw-h-full tw-rounded-full tw-object-cover">
                                            </div>
                                            <textarea rows="1" name="body" placeholder="{{ __('share_your_thoughts_on_this_comment') }}?" class="tw-py-3 tw-px-[18px]"></textarea>
                                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                            @auth('user')
                                                <button type="submit" class="btn btn-primary btn-inline">
                                                    {{ __('post_a_reply') }}
                                                </button>
                                            @else
                                                <button type="submit"
                                                    class="btn btn-primary tw-overflow-visible login_required">
                                                    Post Reply
                                                </button>
                                            @endauth
                                        </div>
                                        <hr>
                                    </form>
                                </div>
                            </li>
                            @if (count($comment->replies) > 0)
                                @foreach ($comment->replies as $reply)
                                    <li class="single-comments">
                                        <div class="rt-single-icon-box rt-mb-15">
                                            <div class="icon-thumb rt-mr-16">
                                                <div class="user-img">
                                                    <img src="{{ url($reply->user->image) }}" alt="user"
                                                         class="object-fit-contain !tw-w-16 !tw-h-16 !tw-rounded-full">
                                                </div>
                                            </div>
                                            <div class="iconbox-content body-font-3 text-gray-700">
                                                <a class="user-name ft-wt-5 rt-mb-4 text-gray-900 hover:text-primary-500"
                                                   href="#">{{ $reply->user->name }}</a>
                                                <span
                                                    class="d-block body-font-4 text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                        <div class="body-font-3 text-gray-700">
                                            {!! nl2br($reply->body) !!}
                                        </div>
                                    </li>
                                @endforeach
                            @endif
                    @empty
                        <p>{{ __('no_comments') }}</p>
                        @endforelse
                    </ul>
                    <div class="rt-spacer-24"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="rt-spacer-80 rt-spacer-md-40"></div>

@endsection

@section('script')
    <script>
        function showHideForm(id) {
            var value = document.getElementById(id).style.display;
            var button = '#replies-' + id.slice(-1);
            if (value == 'none') {
                document.getElementById(id).classList.add('d-none');
                $(button).hide();
            } else {
                document.getElementById(id).classList.remove('d-none');
                $(button).show();
            }
        }
    </script>
    <script>
        function applyJobb(id, name) {
            $('#cvModal').modal('show');
            $('#apply_job_id').val(id);
            $('#apply_job_title').text(name);
        }

        function copyToClipboard(text) {
            var sampleTextarea = document.createElement("textarea");
            document.body.appendChild(sampleTextarea);
            sampleTextarea.value = text; //save main text in it
            sampleTextarea.select(); //select textarea contenrs
            document.execCommand("copy");
            document.body.removeChild(sampleTextarea);
        }

        function copyUrl(value) {
            copyToClipboard(value);
            alert('Copyied to clipboard')
        }
    </script>
    <script>
        function openPopUp(link) {
            var popupWidth = 600;
            var popupHeight = 400;

            var left = (window.innerWidth - popupWidth) / 2 + window.screenX;
            var top = (window.innerHeight - popupHeight) / 2 + window.screenY;

            window.open(link, 'popup', 'width=' + popupWidth + ',height=' + popupHeight + ',left=' + left + ',top=' + top);

        }
    </script>
@endsection
