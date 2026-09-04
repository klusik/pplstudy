<?php

/**
 * Project: PPL Study
 * File: app/controllers/public_home.php
 * Module Type: Public Controller
 *
 * Purpose:
 *   Renders the subject dashboard and imported-bank summary.
 *
 * Author: Rudolf Klusal
 * License: MIT License for application code. See LICENSE.
 */

declare(strict_types=1);

namespace PplStudy\Controllers;

use function PplStudy\Services\question_bank_summary;
use function PplStudy\Services\question_counts_by_subject;
use function PplStudy\Services\subjects_all;
use function PplStudy\Views\render_home;

/** Handle the public home route. */
function public_home(): void
{
    render_home(subjects_all(), question_counts_by_subject(), question_bank_summary());
}
