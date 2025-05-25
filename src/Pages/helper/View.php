<?php

class View {
    private string $lastError = "";

    public function getLastError() : string {
        return $this->lastError;
    }

    public function getHeaderSite( string $faIcon, string $title ) : string {
        $html = "";

        $html .= '<div class="row mb-4 border-bottom pb-3 align-items-center">
            <div class="col-12 col-md-8">
                <a href="/" class="me-3">
                    <img src="/img/logo.png" alt="Logo" class="img-fluid" style="max-width: 350px;">
                </a>
            </div>
            <div class="text-end col-12 col-md-4" id="divTitle">
                <i class="fas '. $faIcon . ' fa-1x text-secondary"></i>
                <span class="ms-1">'. $title .'</span>
            </div>
        </div>';

        return $html;
    }
}