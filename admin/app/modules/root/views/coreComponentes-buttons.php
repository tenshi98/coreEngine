<?php
/** @var string $BASE */  // Variable global para datos de F3
/** @var array $data */   // Variable global para datos de F3
/** @var \F3 $f3 */       // Instancia global de Fat-Free Framework (opcional, si la usas)

?>
<section class="section">

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h1 class="card-title">Normal</h1>

                    <h5 class="card-title">Default Buttons</h5>
                    <button type="button" class="btn btn-primary">Primary</button>
                    <button type="button" class="btn btn-secondary">Secondary</button>
                    <button type="button" class="btn btn-success">Success</button>
                    <button type="button" class="btn btn-danger">Danger</button>
                    <button type="button" class="btn btn-warning">Warning</button>
                    <button type="button" class="btn btn-info">Info</button>
                    <button type="button" class="btn btn-light">Light</button>
                    <button type="button" class="btn btn-dark">Dark</button>
                    <button type="button" class="btn btn-link">Link</button>

                    <h5 class="card-title">Rounded Buttons</h5>
                    <button type="button" class="btn btn-primary rounded-pill">Primary</button>
                    <button type="button" class="btn btn-secondary rounded-pill">Secondary</button>
                    <button type="button" class="btn btn-success rounded-pill">Success</button>
                    <button type="button" class="btn btn-danger rounded-pill">Danger</button>
                    <button type="button" class="btn btn-warning rounded-pill">Warning</button>
                    <button type="button" class="btn btn-info rounded-pill">Info</button>
                    <button type="button" class="btn btn-light rounded-pill">Light</button>
                    <button type="button" class="btn btn-dark rounded-pill">Dark</button>

                    <h5 class="card-title">Icon Buttons</h5>
                    <button type="button" class="btn btn-primary"><i class="bi bi-star me-1"></i> With Text</button>
                    <button type="button" class="btn btn-secondary"><i class="bi bi-collection"></i></button>
                    <button type="button" class="btn btn-success"><i class="bi bi-check-circle"></i></button>
                    <button type="button" class="btn btn-danger"><i class="bi bi-exclamation-octagon"></i></button>
                    <button type="button" class="btn btn-warning"><i class="bi bi-exclamation-triangle"></i></button>
                    <button type="button" class="btn btn-info"><i class="bi bi-info-circle"></i></button>
                    <button type="button" class="btn btn-dark"><i class="bi bi-folder"></i></button>

                    <h5 class="card-title">Button Groups</h5>
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <button type="button" class="btn btn-primary">Left</button>
                        <button type="button" class="btn btn-primary">Middle</button>
                        <button type="button" class="btn btn-primary">Right</button>
                    </div>
                    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                        <button type="button" class="btn btn-danger">Left</button>
                        <button type="button" class="btn btn-warning">Middle</button>
                        <button type="button" class="btn btn-success">Right</button>
                    </div>
                    <div class="btn-group" role="group" aria-label="Basic outlined example">
                        <button type="button" class="btn btn-outline-primary">Left</button>
                        <button type="button" class="btn btn-outline-primary">Middle</button>
                        <button type="button" class="btn btn-outline-primary">Right</button>
                    </div>

                    <h5 class="card-title">Outline Buttons</h5>
                    <button type="button" class="btn btn-outline-primary">Primary</button>
                    <button type="button" class="btn btn-outline-secondary">Secondary</button>
                    <button type="button" class="btn btn-outline-success">Success</button>
                    <button type="button" class="btn btn-outline-danger">Danger</button>
                    <button type="button" class="btn btn-outline-warning">Warning</button>
                    <button type="button" class="btn btn-outline-info">Info</button>
                    <button type="button" class="btn btn-outline-light">Light</button>
                    <button type="button" class="btn btn-outline-dark">Dark</button>

                    <h5 class="card-title">Button Sizes</h5>
                    <button type="button" class="btn btn-primary btn-sm">Small</button>
                    <button type="button" class="btn btn-secondary">Normal</button>
                    <button type="button" class="btn btn-success btn-lg">Large</button>
                    <button type="button" class="btn btn-outline-danger btn-sm">Small</button>
                    <button type="button" class="btn btn-outline-warning">Normal</button>
                    <button type="button" class="btn btn-outline-info btn-lg">Large</button>
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-primary" type="button">Block Button</button>
                    </div>
                    
                    <h5 class="card-title">Button States</h5>
                    <button type="button" class="btn btn-primary">Normal</button>
                    <button type="button" class="btn btn-primary" disabled="">Disabled</button>
                    <button type="button" class="btn btn-outline-primary">Normal</button>
                    <button type="button" class="btn btn-outline-primary" disabled="">Disabled</button>
                </div>
            </div>

            <div class="card" data-aos="fade-up" data-aos-delay="300" data-aos-offset="200" data-aos-duration="500">
                <div class="card-body">
                    <h1 class="card-title">Extras</h1>

                    <h5 class="card-title">Default Buttons</h5>
                    <button type="button" class="btn btn-bkg-red-lighten">btn-bkg-red-lighten</button>
                    <button type="button" class="btn btn-bkg-red-darken">btn-bkg-red-darken</button>
                    <button type="button" class="btn btn-bkg-red-accent">btn-bkg-red-accent</button>
                    <button type="button" class="btn btn-bkg-pink-lighten">btn-bkg-pink-lighten</button>
                    <button type="button" class="btn btn-bkg-pink-darken">btn-bkg-pink-darken</button>
                    <button type="button" class="btn btn-bkg-pink-accent">btn-bkg-pink-accent</button>
                    <button type="button" class="btn btn-bkg-purple-lighten">btn-bkg-purple-lighten</button>
                    <button type="button" class="btn btn-bkg-purple-darken">btn-bkg-purple-darken</button>
                    <button type="button" class="btn btn-bkg-purple-accent">btn-bkg-purple-accent</button>
                    <button type="button" class="btn btn-bkg-deep-purple-lighten">btn-bkg-deep-purple-lighten</button>
                    <button type="button" class="btn btn-bkg-deep-purple-darken">btn-bkg-deep-purple-darken</button>
                    <button type="button" class="btn btn-bkg-deep-purple-accent">btn-bkg-deep-purple-accent</button>
                    <button type="button" class="btn btn-bkg-indigo-lighten">btn-bkg-indigo-lighten</button>
                    <button type="button" class="btn btn-bkg-indigo-darken">btn-bkg-indigo-darken</button>
                    <button type="button" class="btn btn-bkg-indigo-accent">btn-bkg-indigo-accent</button>
                    <button type="button" class="btn btn-bkg-blue-lighten">btn-bkg-blue-lighten</button>
                    <button type="button" class="btn btn-bkg-blue-darken">btn-bkg-blue-darken</button>
                    <button type="button" class="btn btn-bkg-blue-accent">btn-bkg-blue-accent</button>
                    <button type="button" class="btn btn-bkg-light-blue-lighten">btn-bkg-light-blue-lighten</button>
                    <button type="button" class="btn btn-bkg-light-blue-darken">btn-bkg-light-blue-darken</button>
                    <button type="button" class="btn btn-bkg-light-blue-accent">btn-bkg-light-blue-accent</button>
                    <button type="button" class="btn btn-bkg-cyan-lighten">btn-bkg-cyan-lighten</button>
                    <button type="button" class="btn btn-bkg-cyan-darken">btn-bkg-cyan-darken</button>
                    <button type="button" class="btn btn-bkg-cyan-accent">btn-bkg-cyan-accent</button>
                    <button type="button" class="btn btn-bkg-teal-lighten">btn-bkg-teal-lighten</button>
                    <button type="button" class="btn btn-bkg-teal-darken">btn-bkg-teal-darken</button>
                    <button type="button" class="btn btn-bkg-teal-accent">btn-bkg-teal-accent</button>
                    <button type="button" class="btn btn-bkg-green-lighten">btn-bkg-green-lighten</button>
                    <button type="button" class="btn btn-bkg-green-darken">btn-bkg-green-darken</button>
                    <button type="button" class="btn btn-bkg-green-accent">btn-bkg-green-accent</button>
                    <button type="button" class="btn btn-bkg-light-green-lighten">btn-bkg-light-green-lighten</button>
                    <button type="button" class="btn btn-bkg-light-green-darken">btn-bkg-light-green-darken</button>
                    <button type="button" class="btn btn-bkg-light-green-accent">btn-bkg-light-green-accent</button>
                    <button type="button" class="btn btn-bkg-lime-lighten">btn-bkg-lime-lighten</button>
                    <button type="button" class="btn btn-bkg-lime-darken">btn-bkg-lime-darken</button>
                    <button type="button" class="btn btn-bkg-lime-accent">btn-bkg-lime-accent</button>
                    <button type="button" class="btn btn-bkg-yellow-lighten">btn-bkg-yellow-lighten</button>
                    <button type="button" class="btn btn-bkg-yellow-darken">btn-bkg-yellow-darken</button>
                    <button type="button" class="btn btn-bkg-yellow-accent">btn-bkg-yellow-accent</button>
                    <button type="button" class="btn btn-bkg-amber-lighten">btn-bkg-amber-lighten</button>
                    <button type="button" class="btn btn-bkg-amber-darken">btn-bkg-amber-darken</button>
                    <button type="button" class="btn btn-bkg-amber-accent">btn-bkg-amber-accent</button>
                    <button type="button" class="btn btn-bkg-orange-lighten">btn-bkg-orange-lighten</button>
                    <button type="button" class="btn btn-bkg-orange-darken">btn-bkg-orange-darken</button>
                    <button type="button" class="btn btn-bkg-orange-accent">btn-bkg-orange-accent</button>
                    <button type="button" class="btn btn-bkg-deep-orange-lighten">btn-bkg-deep-orange-lighten</button>
                    <button type="button" class="btn btn-bkg-deep-orange-darken">btn-bkg-deep-orange-darken</button>
                    <button type="button" class="btn btn-bkg-deep-orange-accent">btn-bkg-deep-orange-accent</button>
                    <button type="button" class="btn btn-bkg-brown-lighten">btn-bkg-brown-lighten</button>
                    <button type="button" class="btn btn-bkg-brown-darken">btn-bkg-brown-darken</button>
                    <button type="button" class="btn btn-bkg-blue-grey-lighten">btn-bkg-blue-grey-lighten</button>
                    <button type="button" class="btn btn-bkg-blue-grey-darken">btn-bkg-blue-grey-darken</button>
                    <button type="button" class="btn btn-bkg-grey-lighten">btn-bkg-grey-lighten</button>
                    <button type="button" class="btn btn-bkg-grey-darken">btn-bkg-grey-darken</button>


                    <h5 class="card-title">Outline Buttons</h5>
                    <button type="button" class="btn btn-outline-bkg-red-lighten">btn-outline-bkg-red-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-red-darken">btn-outline-bkg-red-darken</button>
                    <button type="button" class="btn btn-outline-bkg-red-accent">btn-outline-bkg-red-accent</button>
                    <button type="button" class="btn btn-outline-bkg-pink-lighten">btn-outline-bkg-pink-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-pink-darken">btn-outline-bkg-pink-darken</button>
                    <button type="button" class="btn btn-outline-bkg-pink-accent">btn-outline-bkg-pink-accent</button>
                    <button type="button" class="btn btn-outline-bkg-purple-lighten">btn-outline-bkg-purple-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-purple-darken">btn-outline-bkg-purple-darken</button>
                    <button type="button" class="btn btn-outline-bkg-purple-accent">btn-outline-bkg-purple-accent</button>
                    <button type="button" class="btn btn-outline-bkg-deep-purple-lighten">btn-outline-bkg-deep-purple-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-deep-purple-darken">btn-outline-bkg-deep-purple-darken</button>
                    <button type="button" class="btn btn-outline-bkg-deep-purple-accent">btn-outline-bkg-deep-purple-accent</button>
                    <button type="button" class="btn btn-outline-bkg-indigo-lighten">btn-outline-bkg-indigo-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-indigo-darken">btn-outline-bkg-indigo-darken</button>
                    <button type="button" class="btn btn-outline-bkg-indigo-accent">btn-outline-bkg-indigo-accent</button>
                    <button type="button" class="btn btn-outline-bkg-blue-lighten">btn-outline-bkg-blue-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-blue-darken">btn-outline-bkg-blue-darken</button>
                    <button type="button" class="btn btn-outline-bkg-blue-accent">btn-outline-bkg-blue-accent</button>
                    <button type="button" class="btn btn-outline-bkg-light-blue-lighten">btn-outline-bkg-light-blue-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-light-blue-darken">btn-outline-bkg-light-blue-darken</button>
                    <button type="button" class="btn btn-outline-bkg-light-blue-accent">btn-outline-bkg-light-blue-accent</button>
                    <button type="button" class="btn btn-outline-bkg-cyan-lighten">btn-outline-bkg-cyan-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-cyan-darken">btn-outline-bkg-cyan-darken</button>
                    <button type="button" class="btn btn-outline-bkg-cyan-accent">btn-outline-bkg-cyan-accent</button>
                    <button type="button" class="btn btn-outline-bkg-teal-lighten">btn-outline-bkg-teal-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-teal-darken">btn-outline-bkg-teal-darken</button>
                    <button type="button" class="btn btn-outline-bkg-teal-accent">btn-outline-bkg-teal-accent</button>
                    <button type="button" class="btn btn-outline-bkg-green-lighten">btn-outline-bkg-green-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-green-darken">btn-outline-bkg-green-darken</button>
                    <button type="button" class="btn btn-outline-bkg-green-accent">btn-outline-bkg-green-accent</button>
                    <button type="button" class="btn btn-outline-bkg-light-green-lighten">btn-outline-bkg-light-green-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-light-green-darken">btn-outline-bkg-light-green-darken</button>
                    <button type="button" class="btn btn-outline-bkg-light-green-accent">btn-outline-bkg-light-green-accent</button>
                    <button type="button" class="btn btn-outline-bkg-lime-lighten">btn-outline-bkg-lime-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-lime-darken">btn-outline-bkg-lime-darken</button>
                    <button type="button" class="btn btn-outline-bkg-lime-accent">btn-outline-bkg-lime-accent</button>
                    <button type="button" class="btn btn-outline-bkg-yellow-lighten">btn-outline-bkg-yellow-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-yellow-darken">btn-outline-bkg-yellow-darken</button>
                    <button type="button" class="btn btn-outline-bkg-yellow-accent">btn-outline-bkg-yellow-accent</button>
                    <button type="button" class="btn btn-outline-bkg-amber-lighten">btn-outline-bkg-amber-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-amber-darken">btn-outline-bkg-amber-darken</button>
                    <button type="button" class="btn btn-outline-bkg-amber-accent">btn-outline-bkg-amber-accent</button>
                    <button type="button" class="btn btn-outline-bkg-orange-lighten">btn-outline-bkg-orange-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-orange-darken">btn-outline-bkg-orange-darken</button>
                    <button type="button" class="btn btn-outline-bkg-orange-accent">btn-outline-bkg-orange-accent</button>
                    <button type="button" class="btn btn-outline-bkg-deep-orange-lighten">btn-outline-bkg-deep-orange-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-deep-orange-darken">btn-outline-bkg-deep-orange-darken</button>
                    <button type="button" class="btn btn-outline-bkg-deep-orange-accent">btn-outline-bkg-deep-orange-accent</button>
                    <button type="button" class="btn btn-outline-bkg-brown-lighten">btn-outline-bkg-brown-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-brown-darken">btn-outline-bkg-brown-darken</button>
                    <button type="button" class="btn btn-outline-bkg-blue-grey-lighten">btn-outline-bkg-blue-grey-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-blue-grey-darken">btn-outline-bkg-blue-grey-darken</button>
                    <button type="button" class="btn btn-outline-bkg-grey-lighten">btn-outline-bkg-grey-lighten</button>
                    <button type="button" class="btn btn-outline-bkg-grey-darken">btn-outline-bkg-grey-darken</button>


                    <h5 class="card-title">Button Disabled</h5>
                    <button type="button" class="btn btn-bkg-red-lighten"          disabled="">btn-bkg-red-lighten</button>
                    <button type="button" class="btn btn-bkg-red-darken"           disabled="">btn-bkg-red-darken</button>
                    <button type="button" class="btn btn-bkg-red-accent"           disabled="">btn-bkg-red-accent</button>
                    <button type="button" class="btn btn-bkg-pink-lighten"         disabled="">btn-bkg-pink-lighten</button>
                    <button type="button" class="btn btn-bkg-pink-darken"          disabled="">btn-bkg-pink-darken</button>
                    <button type="button" class="btn btn-bkg-pink-accent"          disabled="">btn-bkg-pink-accent</button>
                    <button type="button" class="btn btn-bkg-purple-lighten"       disabled="">btn-bkg-purple-lighten</button>
                    <button type="button" class="btn btn-bkg-purple-darken"        disabled="">btn-bkg-purple-darken</button>
                    <button type="button" class="btn btn-bkg-purple-accent"        disabled="">btn-bkg-purple-accent</button>
                    <button type="button" class="btn btn-bkg-deep-purple-lighten"  disabled="">btn-bkg-deep-purple-lighten</button>
                    <button type="button" class="btn btn-bkg-deep-purple-darken"   disabled="">btn-bkg-deep-purple-darken</button>
                    <button type="button" class="btn btn-bkg-deep-purple-accent"   disabled="">btn-bkg-deep-purple-accent</button>
                    <button type="button" class="btn btn-bkg-indigo-lighten"       disabled="">btn-bkg-indigo-lighten</button>
                    <button type="button" class="btn btn-bkg-indigo-darken"        disabled="">btn-bkg-indigo-darken</button>
                    <button type="button" class="btn btn-bkg-indigo-accent"        disabled="">btn-bkg-indigo-accent</button>
                    <button type="button" class="btn btn-bkg-blue-lighten"         disabled="">btn-bkg-blue-lighten</button>
                    <button type="button" class="btn btn-bkg-blue-darken"          disabled="">btn-bkg-blue-darken</button>
                    <button type="button" class="btn btn-bkg-blue-accent"          disabled="">btn-bkg-blue-accent</button>
                    <button type="button" class="btn btn-bkg-light-blue-lighten"   disabled="">btn-bkg-light-blue-lighten</button>
                    <button type="button" class="btn btn-bkg-light-blue-darken"    disabled="">btn-bkg-light-blue-darken</button>
                    <button type="button" class="btn btn-bkg-light-blue-accent"    disabled="">btn-bkg-light-blue-accent</button>
                    <button type="button" class="btn btn-bkg-cyan-lighten"         disabled="">btn-bkg-cyan-lighten</button>
                    <button type="button" class="btn btn-bkg-cyan-darken"          disabled="">btn-bkg-cyan-darken</button>
                    <button type="button" class="btn btn-bkg-cyan-accent"          disabled="">btn-bkg-cyan-accent</button>
                    <button type="button" class="btn btn-bkg-teal-lighten"         disabled="">btn-bkg-teal-lighten</button>
                    <button type="button" class="btn btn-bkg-teal-darken"          disabled="">btn-bkg-teal-darken</button>
                    <button type="button" class="btn btn-bkg-teal-accent"          disabled="">btn-bkg-teal-accent</button>
                    <button type="button" class="btn btn-bkg-green-lighten"        disabled="">btn-bkg-green-lighten</button>
                    <button type="button" class="btn btn-bkg-green-darken"         disabled="">btn-bkg-green-darken</button>
                    <button type="button" class="btn btn-bkg-green-accent"         disabled="">btn-bkg-green-accent</button>
                    <button type="button" class="btn btn-bkg-light-green-lighten"  disabled="">btn-bkg-light-green-lighten</button>
                    <button type="button" class="btn btn-bkg-light-green-darken"   disabled="">btn-bkg-light-green-darken</button>
                    <button type="button" class="btn btn-bkg-light-green-accent"   disabled="">btn-bkg-light-green-accent</button>
                    <button type="button" class="btn btn-bkg-lime-lighten"         disabled="">btn-bkg-lime-lighten</button>
                    <button type="button" class="btn btn-bkg-lime-darken"          disabled="">btn-bkg-lime-darken</button>
                    <button type="button" class="btn btn-bkg-lime-accent"          disabled="">btn-bkg-lime-accent</button>
                    <button type="button" class="btn btn-bkg-yellow-lighten"       disabled="">btn-bkg-yellow-lighten</button>
                    <button type="button" class="btn btn-bkg-yellow-darken"        disabled="">btn-bkg-yellow-darken</button>
                    <button type="button" class="btn btn-bkg-yellow-accent"        disabled="">btn-bkg-yellow-accent</button>
                    <button type="button" class="btn btn-bkg-amber-lighten"        disabled="">btn-bkg-amber-lighten</button>
                    <button type="button" class="btn btn-bkg-amber-darken"         disabled="">btn-bkg-amber-darken</button>
                    <button type="button" class="btn btn-bkg-amber-accent"         disabled="">btn-bkg-amber-accent</button>
                    <button type="button" class="btn btn-bkg-orange-lighten"       disabled="">btn-bkg-orange-lighten</button>
                    <button type="button" class="btn btn-bkg-orange-darken"        disabled="">btn-bkg-orange-darken</button>
                    <button type="button" class="btn btn-bkg-orange-accent"        disabled="">btn-bkg-orange-accent</button>
                    <button type="button" class="btn btn-bkg-deep-orange-lighten"  disabled="">btn-bkg-deep-orange-lighten</button>
                    <button type="button" class="btn btn-bkg-deep-orange-darken"   disabled="">btn-bkg-deep-orange-darken</button>
                    <button type="button" class="btn btn-bkg-deep-orange-accent"   disabled="">btn-bkg-deep-orange-accent</button>
                    <button type="button" class="btn btn-bkg-brown-lighten"        disabled="">btn-bkg-brown-lighten</button>
                    <button type="button" class="btn btn-bkg-brown-darken"         disabled="">btn-bkg-brown-darken</button>
                    <button type="button" class="btn btn-bkg-blue-grey-lighten"    disabled="">btn-bkg-blue-grey-lighten</button>
                    <button type="button" class="btn btn-bkg-blue-grey-darken"     disabled="">btn-bkg-blue-grey-darken</button>
                    <button type="button" class="btn btn-bkg-grey-lighten"         disabled="">btn-bkg-grey-lighten</button>
                    <button type="button" class="btn btn-bkg-grey-darken"          disabled="">btn-bkg-grey-darken</button>

                    <h5 class="card-title">Flashing Buttons</h5>
                    <button type="button" class="btn btn-bkg-red-lighten          pulseBtn-time-1s  pulseBtn-red-lighten">btn-bkg-red-lighten</button>
                    <button type="button" class="btn btn-bkg-red-darken           pulseBtn-time-3s  pulseBtn-red-darken">btn-bkg-red-darken</button>
                    <button type="button" class="btn btn-bkg-red-accent           pulseBtn-time-5s  pulseBtn-red-accent">btn-bkg-red-accent</button>
                    <button type="button" class="btn btn-bkg-pink-lighten         pulseBtn-time-10s pulseBtn-pink-lighten">btn-bkg-pink-lighten</button>
                    <button type="button" class="btn btn-bkg-pink-darken          pulseBtn-time-1s  pulseBtn-pink-darken">btn-bkg-pink-darken</button>
                    <button type="button" class="btn btn-bkg-pink-accent          pulseBtn-time-1s  pulseBtn-pink-accent">btn-bkg-pink-accent</button>
                    <button type="button" class="btn btn-bkg-purple-lighten       pulseBtn-time-1s  pulseBtn-purple-lighten">btn-bkg-purple-lighten</button>
                    <button type="button" class="btn btn-bkg-purple-darken        pulseBtn-time-1s  pulseBtn-purple-darken">btn-bkg-purple-darken</button>
                    <button type="button" class="btn btn-bkg-purple-accent        pulseBtn-time-1s  pulseBtn-purple-accent">btn-bkg-purple-accent</button>
                    <button type="button" class="btn btn-bkg-deep-purple-lighten  pulseBtn-time-1s  pulseBtn-deep-purple-lighten">btn-bkg-deep-purple-lighten</button>
                    <button type="button" class="btn btn-bkg-deep-purple-darken   pulseBtn-time-1s  pulseBtn-deep-purple-darken">btn-bkg-deep-purple-darken</button>
                    <button type="button" class="btn btn-bkg-deep-purple-accent   pulseBtn-time-1s  pulseBtn-deep-purple-accent">btn-bkg-deep-purple-accent</button>
                    <button type="button" class="btn btn-bkg-indigo-lighten       pulseBtn-time-1s  pulseBtn-indigo-lighten">btn-bkg-indigo-lighten</button>
                    <button type="button" class="btn btn-bkg-indigo-darken        pulseBtn-time-1s  pulseBtn-indigo-darken">btn-bkg-indigo-darken</button>
                    <button type="button" class="btn btn-bkg-indigo-accent        pulseBtn-time-1s  pulseBtn-indigo-accent">btn-bkg-indigo-accent</button>
                    <button type="button" class="btn btn-bkg-blue-lighten         pulseBtn-time-1s  pulseBtn-blue-lighten">btn-bkg-blue-lighten</button>
                    <button type="button" class="btn btn-bkg-blue-darken          pulseBtn-time-1s  pulseBtn-blue-darken">btn-bkg-blue-darken</button>
                    <button type="button" class="btn btn-bkg-blue-accent          pulseBtn-time-1s  pulseBtn-blue-accent">btn-bkg-blue-accent</button>
                    <button type="button" class="btn btn-bkg-light-blue-lighten   pulseBtn-time-1s  pulseBtn-light-blue-lighten">btn-bkg-light-blue-lighten</button>
                    <button type="button" class="btn btn-bkg-light-blue-darken    pulseBtn-time-1s  pulseBtn-light-blue-darken">btn-bkg-light-blue-darken</button>
                    <button type="button" class="btn btn-bkg-light-blue-accent    pulseBtn-time-1s  pulseBtn-light-blue-accent">btn-bkg-light-blue-accent</button>
                    <button type="button" class="btn btn-bkg-cyan-lighten         pulseBtn-time-1s  pulseBtn-cyan-lighten">btn-bkg-cyan-lighten</button>
                    <button type="button" class="btn btn-bkg-cyan-darken          pulseBtn-time-1s  pulseBtn-cyan-darken">btn-bkg-cyan-darken</button>
                    <button type="button" class="btn btn-bkg-cyan-accent          pulseBtn-time-1s  pulseBtn-cyan-accent">btn-bkg-cyan-accent</button>
                    <button type="button" class="btn btn-bkg-teal-lighten         pulseBtn-time-1s  pulseBtn-teal-lighten">btn-bkg-teal-lighten</button>
                    <button type="button" class="btn btn-bkg-teal-darken          pulseBtn-time-1s  pulseBtn-teal-darken">btn-bkg-teal-darken</button>
                    <button type="button" class="btn btn-bkg-teal-accent          pulseBtn-time-1s  pulseBtn-teal-accent">btn-bkg-teal-accent</button>
                    <button type="button" class="btn btn-bkg-green-lighten        pulseBtn-time-1s  pulseBtn-green-lighten">btn-bkg-green-lighten</button>
                    <button type="button" class="btn btn-bkg-green-darken         pulseBtn-time-1s  pulseBtn-green-darken">btn-bkg-green-darken</button>
                    <button type="button" class="btn btn-bkg-green-accent         pulseBtn-time-1s  pulseBtn-green-accent">btn-bkg-green-accent</button>
                    <button type="button" class="btn btn-bkg-light-green-lighten  pulseBtn-time-1s  pulseBtn-light-green-lighten">btn-bkg-light-green-lighten</button>
                    <button type="button" class="btn btn-bkg-light-green-darken   pulseBtn-time-1s  pulseBtn-light-green-darken">btn-bkg-light-green-darken</button>
                    <button type="button" class="btn btn-bkg-light-green-accent   pulseBtn-time-1s  pulseBtn-light-green-accent">btn-bkg-light-green-accent</button>
                    <button type="button" class="btn btn-bkg-lime-lighten         pulseBtn-time-1s  pulseBtn-lime-lighten">btn-bkg-lime-lighten</button>
                    <button type="button" class="btn btn-bkg-lime-darken          pulseBtn-time-1s  pulseBtn-lime-darken">btn-bkg-lime-darken</button>
                    <button type="button" class="btn btn-bkg-lime-accent          pulseBtn-time-1s  pulseBtn-lime-accent">btn-bkg-lime-accent</button>
                    <button type="button" class="btn btn-bkg-yellow-lighten       pulseBtn-time-1s  pulseBtn-yellow-lighten">btn-bkg-yellow-lighten</button>
                    <button type="button" class="btn btn-bkg-yellow-darken        pulseBtn-time-1s  pulseBtn-yellow-darken">btn-bkg-yellow-darken</button>
                    <button type="button" class="btn btn-bkg-yellow-accent        pulseBtn-time-1s  pulseBtn-yellow-accent">btn-bkg-yellow-accent</button>
                    <button type="button" class="btn btn-bkg-amber-lighten        pulseBtn-time-1s  pulseBtn-amber-lighten">btn-bkg-amber-lighten</button>
                    <button type="button" class="btn btn-bkg-amber-darken         pulseBtn-time-1s  pulseBtn-amber-darken">btn-bkg-amber-darken</button>
                    <button type="button" class="btn btn-bkg-amber-accent         pulseBtn-time-1s  pulseBtn-amber-accent">btn-bkg-amber-accent</button>
                    <button type="button" class="btn btn-bkg-orange-lighten       pulseBtn-time-1s  pulseBtn-orange-lighten">btn-bkg-orange-lighten</button>
                    <button type="button" class="btn btn-bkg-orange-darken        pulseBtn-time-1s  pulseBtn-orange-darken">btn-bkg-orange-darken</button>
                    <button type="button" class="btn btn-bkg-orange-accent        pulseBtn-time-1s  pulseBtn-orange-accent">btn-bkg-orange-accent</button>
                    <button type="button" class="btn btn-bkg-deep-orange-lighten  pulseBtn-time-1s  pulseBtn-deep-orange-lighten">btn-bkg-deep-orange-lighten</button>
                    <button type="button" class="btn btn-bkg-deep-orange-darken   pulseBtn-time-1s  pulseBtn-deep-orange-darken">btn-bkg-deep-orange-darken</button>
                    <button type="button" class="btn btn-bkg-deep-orange-accent   pulseBtn-time-1s  pulseBtn-deep-orange-accent">btn-bkg-deep-orange-accent</button>
                    <button type="button" class="btn btn-bkg-brown-lighten        pulseBtn-time-1s  pulseBtn-brown-lighten">btn-bkg-brown-lighten</button>
                    <button type="button" class="btn btn-bkg-brown-darken         pulseBtn-time-1s  pulseBtn-brown-darken">btn-bkg-brown-darken</button>
                    <button type="button" class="btn btn-bkg-blue-grey-lighten    pulseBtn-time-1s  pulseBtn-blue-grey-lighten">btn-bkg-blue-grey-lighten</button>
                    <button type="button" class="btn btn-bkg-blue-grey-darken     pulseBtn-time-1s  pulseBtn-blue-grey-darken">btn-bkg-blue-grey-darken</button>
                    <button type="button" class="btn btn-bkg-grey-lighten         pulseBtn-time-1s  pulseBtn-grey-lighten">btn-bkg-grey-lighten</button>
                    <button type="button" class="btn btn-bkg-grey-darken          pulseBtn-time-1s  pulseBtn-grey-darken">btn-bkg-grey-darken</button>


                </div>
            </div>



        </div>
    </div>

</section>
