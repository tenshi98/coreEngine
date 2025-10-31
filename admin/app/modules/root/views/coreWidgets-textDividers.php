<section class="section dashboard">
    <div class="row">


        <div class="col-md-12 mb-4">
            <div class="card">
                <h5 class="card-header">Basic</h5>
                <div class="card-body">
                    <div class="divider">
                        <div class="divider-text">Text</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 mb-4">
            <div class="card">
                <h5 class="card-header">Alignment</h5>
                <div class="card-body">
                    <div class="divider text-start">
                        <div class="divider-text">Start</div>
                    </div>
                    <div class="divider text-start-center">
                        <div class="divider-text">Start-Center</div>
                    </div>
                    <div class="divider">
                        <div class="divider-text">Center (Default)</div>
                    </div>
                    <div class="divider text-end-center">
                        <div class="divider-text">End-Center</div>
                    </div>
                    <div class="divider text-end">
                        <div class="divider-text">End</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 mb-4">
            <div class="card">
                <h5 class="card-header">Colors</h5>
                <div class="card-body">
                    <div class="divider divider-primary">
                        <div class="divider-text">Primary</div>
                    </div>
                    <div class="divider divider-success">
                        <div class="divider-text">Success</div>
                    </div>
                    <div class="divider divider-danger">
                        <div class="divider-text">Danger</div>
                    </div>
                    <div class="divider divider-warning">
                        <div class="divider-text">Warning</div>
                    </div>
                    <div class="divider divider-info">
                        <div class="divider-text">Info</div>
                    </div>
                    <div class="divider divider-dark">
                        <div class="divider-text">Dark</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 mb-4">
            <div class="card">
                <h5 class="card-header">Icons</h5>
                <div class="card-body">
                    <div class="divider text-start">
                        <div class="divider-text">
                            <i class="bx bx-sun"></i>
                        </div>
                    </div>
                    <div class="divider text-start-center">
                        <div class="divider-text">
                            <i class="bx bx-crown"></i>
                        </div>
                    </div>
                    <div class="divider">
                        <div class="divider-text">
                            <i class="bx bx-star"></i>
                        </div>
                    </div>
                    <div class="divider text-end-center">
                        <div class="divider-text">
                            <i class="bx bx-coffee-togo"></i>
                        </div>
                    </div>
                    <div class="divider text-end">
                        <div class="divider-text">
                            <i class="bx bx-cut bx-rotate-180"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <h5 class="card-header">Styles</h5>
                <div class="card-body">
                    <div class="divider">
                        <div class="divider-text">Solid (Default)</div>
                    </div>
                    <div class="divider divider-dotted">
                        <div class="divider-text">Dotted</div>
                    </div>
                    <div class="divider divider-dashed">
                        <div class="divider-text">Dashed</div>
                    </div>
                </div>
            </div>
        </div>

<style>
.divider {display: block;text-align: center;margin: 1rem 0;overflow: hidden;white-space: nowrap;color: #697a8d;}
.divider .divider-text {position: relative;display: inline-block;font-size: 0.8rem;padding: 0rem 1rem;}
.divider.text-start-center .divider-text {left: -25%;}
.divider.text-end-center .divider-text {right: -25%;}
.divider.text-end .divider-text {padding-right: 0;}
.divider.text-start .divider-text {padding-left: 0;}
.divider .divider-text i {font-size: 1rem;}
.divider .divider-text::before {right: 100%;}
.divider .divider-text::after {left: 100%;}
.divider .divider-text::before, .divider .divider-text::after {content: "";position: absolute;top: 50%;width: 100vw;border-top: 1px solid rgba(67, 89, 113, 0.2);border-top-color: rgba(67, 89, 113, 0.2);}
.divider.divider-dotted .divider-text::before, .divider.divider-dotted .divider-text::after {border-style: dotted;border-width: 0 1px 1px;border-color: rgba(67, 89, 113, 0.2);}
.divider.divider-dashed .divider-text::before, .divider.divider-dashed .divider-text::after {border-style: dashed;border-width: 0 1px 1px;border-color: rgba(67, 89, 113, 0.2);}
.divider.divider-primary .divider-text::before, .divider.divider-primary .divider-text::after {border-color: var(--btn-primary-border);}
.divider.divider.divider-success .divider-text::before, .divider.divider.divider-success .divider-text::after {border-color: var(--btn-success-border);}
.divider.divider.divider-danger .divider-text::before, .divider.divider.divider-danger .divider-text::after {border-color: var(--btn-danger-border);}
.divider.divider.divider-warning .divider-text::before, .divider.divider.divider-warning .divider-text::after {border-color: var(--btn-warning-border);}
.divider.divider.divider-info .divider-text::before, .divider.divider.divider-info .divider-text::after {border-color: var(--btn-info-border);}
.divider.divider.divider-dark .divider-text::before, .divider.divider.divider-dark .divider-text::after {border-color: var(--btn-dark-border);}


</style>

    </div>
</section>