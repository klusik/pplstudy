<?php

/**
 * Project: PPL Study
 * File: app/controllers/public_about.php
 * Module Type: Public Controller
 *
 * Purpose:
 *   Renders source provenance and the paper structure stored with subjects.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use function PplStudy\Services\subjects_all;
use function PplStudy\Views\render_about;

/** Handle the public source-information route. */
function public_about(): void
{
    render_about(subjects_all());
}
