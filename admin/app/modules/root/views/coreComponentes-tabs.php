<section class="section">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-4">

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Default Tabs</h5>
                    <?php
                    $Options = [
                        'type'      => 1,
                        'justif'    => 1,
                        'activeTab' => 1,
                        'arrData'   => $data['arrData']
                    ];
                    $data['Fnc_WidgetsCommon']->tabs($Options); ?>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Inverted Tabs</h5>
                    <?php
                    $Options = [
                        'type'      => 2,
                        'justif'    => 1,
                        'activeTab' => 1,
                        'arrData'   => $data['arrData']
                    ];
                    $data['Fnc_WidgetsCommon']->tabs($Options); ?>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Complement Tabs</h5>
                    <?php
                    $Options = [
                        'type'      => 3,
                        'justif'    => 1,
                        'activeTab' => 1,
                        'arrData'   => $data['arrData']
                    ];
                    $data['Fnc_WidgetsCommon']->tabs($Options); ?>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Bordered Tabs</h5>
                    <?php
                    $Options = [
                        'type'      => 4,
                        'justif'    => 1,
                        'activeTab' => 1,
                        'arrData'   => $data['arrData']
                    ];
                    $data['Fnc_WidgetsCommon']->tabs($Options); ?>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Pills Tabs</h5>

                    <!-- Pills Tabs -->
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Home</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Profile</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Contact</button>
                        </li>
                    </ul>
                    <div class="tab-content pt-2" id="myTabContent">
                        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="home-tab">
                            Sunt est soluta temporibus accusantium neque nam maiores cumque temporibus. Tempora libero non est unde veniam est qui dolor. Ut sunt iure rerum quae quisquam autem eveniet perspiciatis odit. Fuga sequi sed ea saepe at unde.
                        </div>
                        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="profile-tab">
                            Nesciunt totam et. Consequuntur magnam aliquid eos nulla dolor iure eos quia. Accusantium distinctio omnis et atque fugiat. Itaque doloremque aliquid sint quasi quia distinctio similique. Voluptate nihil recusandae mollitia dolores. Ut laboriosam voluptatum dicta.
                        </div>
                        <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="contact-tab">
                            Saepe animi et soluta ad odit soluta sunt. Nihil quos omnis animi debitis cumque. Accusantium quibusdam perspiciatis qui qui omnis magnam. Officiis accusamus impedit molestias nostrum veniam. Qui amet ipsum iure. Dignissimos fuga tempore dolor.
                        </div>
                    </div><!-- End Pills Tabs -->

                </div>
            </div>

        </div>

        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-4">

            <div class="card" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Default Tabs Justified</h5>
                    <?php
                    $Options = [
                        'type'      => 1,
                        'justif'    => 2,
                        'activeTab' => 1,
                        'arrData'   => $data['arrData']
                    ];
                    $data['Fnc_WidgetsCommon']->tabs($Options); ?>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Inverted Tabs Justified</h5>
                    <?php
                    $Options = [
                        'type'      => 2,
                        'justif'    => 2,
                        'activeTab' => 1,
                        'arrData'   => $data['arrData']
                    ];
                    $data['Fnc_WidgetsCommon']->tabs($Options); ?>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Complement Tabs Justified</h5>
                    <?php
                    $Options = [
                        'type'      => 3,
                        'justif'    => 2,
                        'activeTab' => 1,
                        'arrData'   => $data['arrData']
                    ];
                    $data['Fnc_WidgetsCommon']->tabs($Options); ?>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Bordered Tabs Justified</h5>
                    <?php
                    $Options = [
                        'type'      => 4,
                        'justif'    => 2,
                        'activeTab' => 1,
                        'arrData'   => $data['arrData']
                    ];
                    $data['Fnc_WidgetsCommon']->tabs($Options); ?>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="600" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h5 class="card-title">Vertical Pills Tabs</h5>

                    <!-- Vertical Pills Tabs -->
                    <div class="d-flex align-items-start">
                        <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <button class="nav-link active" id="v-pills-home-tab" data-bs-toggle="pill" data-bs-target="#v-pills-home" type="button" role="tab" aria-controls="v-pills-home" aria-selected="true">Home</button>
                            <button class="nav-link" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="false">Profile</button>
                            <button class="nav-link" id="v-pills-messages-tab" data-bs-toggle="pill" data-bs-target="#v-pills-messages" type="button" role="tab" aria-controls="v-pills-messages" aria-selected="false">Messages</button>
                        </div>
                        <div class="tab-content" id="v-pills-tabContent">
                            <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                Sunt est soluta temporibus accusantium neque nam maiores cumque temporibus. Tempora libero non est unde veniam est qui dolor. Ut sunt iure rerum quae quisquam autem eveniet perspiciatis odit. Fuga sequi sed ea saepe at unde.
                            </div>
                            <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                Nesciunt totam et. Consequuntur magnam aliquid eos nulla dolor iure eos quia. Accusantium distinctio omnis et atque fugiat. Itaque doloremque aliquid sint quasi quia distinctio similique. Voluptate nihil recusandae mollitia dolores. Ut laboriosam voluptatum dicta.
                            </div>
                            <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
                                Saepe animi et soluta ad odit soluta sunt. Nihil quos omnis animi debitis cumque. Accusantium quibusdam perspiciatis qui qui omnis magnam. Officiis accusamus impedit molestias nostrum veniam. Qui amet ipsum iure. Dignissimos fuga tempore dolor.
                            </div>
                        </div>
                    </div>
                    <!-- End Vertical Pills Tabs -->

                </div>
            </div>

        </div>

    </div>
</section>