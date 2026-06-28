<?php
$settings = DB::table('settings')->first();
?>
<section class="footer-7 section-top-bottom-padding">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <div class="footer-bottom">
                            <div class="footer-link">
                                <div class="f-info">
                                    <ul class="footer-first">
                                        <li class="logo-content">
                                            <a href="{{route('product')}}">
                                                <img src="{{url('/')}}/uploads/setting/<?php echo $settings->id; ?>/<?php echo $settings->company_footer_logo; ?>" class="img-fluid f-logo-image" alt="logo-image" width="200">
                                            </a>
                                        </li>
                                        <li class="logo-content footer-details">
                                            <ul class="f-map">
                                                <li class="map-icn"><i class="fas fa-map-marker-alt"></i></li>
                                                <li class="map-text">
                                                    <?php echo $settings->company_address; ?>
                                                </li>
                                            </ul>
                                            <ul class="f-contact">
                                                <li class="call-icn"><i class="fas fa-phone"></i></li>
                                                <li class="contact-link">
                                                    <a href="tel:{{$settings->company_phone}}">Phone: {{$settings->company_phone}}</a>
                                                    <a href="mailto:{{$settings->company_email}}">Email: {{$settings->company_email}}</a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                                <div class="f-link">
                                    <h2 class="h-footer">{{ __('frontend.services') }}</h2>
                                    <a href="#services" data-bs-toggle="collapse" class="h-footer">
                                        <span>{{ __('frontend.services') }}</span>
                                        <i class="fa fa-angle-down"></i>
                                    </a>
                                    <ul class="f-link-ul collapse" id="services" data-bs-parent="#footer-accordian">
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.about') }}</a></li>
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.faqs') }}</a></li>
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.contact_us') }}</a></li>
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.news') }}</a></li>
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.sitemap') }}</a></li>
                                    </ul>
                                </div>
                                <div class="f-link">
                                    <h2 class="h-footer">{{ __('frontend.privacy_terms') }}</h2>
                                    <a href="#privacy" data-bs-toggle="collapse" class="h-footer">
                                        <span>{{ __('frontend.privacy_terms') }}</span>
                                        <i class="fa fa-angle-down"></i>
                                    </a>
                                    <ul class="f-link-ul collapse" id="privacy" data-bs-parent="#footer-accordian">
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.payment_policy') }}</a></li>
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.privacy_policy') }}</a></li>
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.return_policy') }}</a></li>
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.shipping_policy') }}</a></li>
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.terms_conditions') }}</a></li>
                                    </ul>
                                </div>
                                <div class="f-link">
                                    <h2 class="h-footer">{{ __('frontend.my_account') }}</h2>
                                    <a href="#account" data-bs-toggle="collapse" class="h-footer">
                                        <span>{{ __('frontend.my_account') }}</span>
                                        <i class="fa fa-angle-down"></i>
                                    </a>
                                    <ul class="f-link-ul collapse" id="account" data-bs-parent="#footer-accordian">
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.my_account') }}</a></li>
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.my_cart') }}</a></li>
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.order_history') }}</a></li>
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.my_wishlist') }}</a></li>
                                        <li class="f-link-ul-li"><a href="#">{{ __('frontend.my_address') }}</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- footer end -->
        <!-- copyright start -->
        <section class="footer-copyright">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <div class="f-bottom">
                            <p><i class="fa fa-copyright"></i> {{date('Y')}} e-regio. {{ __('frontend.copyright') }}</p>                          
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- copyright end -->