<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Invoices API Documentation',
    description: 'API Documentation for Invoices management system',
    contact: new OA\Contact(name: 'Thiago', email: 'thiiagoms@proton.me'),
    license: new OA\License(
        name: 'Apache 2.0',
        url: 'http://www.apache.org/licenses/LICENSE-2.0.html'
    )
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
)]
#[OA\Server(
    url: 'http://localhost:8000/api/documentation',
    description: 'API Documentation Server'
)]
#[OA\Tag(
    name: 'Projects',
    description: 'API Endpoints of Projects'
)]
abstract class Controller
{
    //
}
