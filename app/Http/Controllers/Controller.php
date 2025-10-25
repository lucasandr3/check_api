<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="2.0.0",
 *     title="Fleet Checklist API",
 *     description="API para Sistema de Checklist de Frotas e Equipamentos com Controle de Pneus",
 *     @OA\Contact(
 *         email="suporte@fleetchecklist.com",
 *         name="Suporte Fleet Checklist"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Fleet Checklist API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Tag(
 *     name="Autenticação",
 *     description="Endpoints para autenticação JWT"
 * )
 * 
 * @OA\Tag(
 *     name="Checklists",
 *     description="Endpoints para gerenciamento de checklists de frotas e equipamentos"
 * )
 * 
 * @OA\Tag(
 *     name="ChecklistTemplate",
 *     description="Endpoints para gerenciamento de templates de checklist"
 * )
 * 
 * @OA\Tag(
 *     name="Equipment",
 *     description="Endpoints para gestão de equipamentos"
 * )
 * 
 * @OA\Tag(
 *     name="Tires",
 *     description="Endpoints para controle de pneus"
 * )
 * 
 * @OA\Tag(
 *     name="Dashboard",
 *     description="Endpoints para estatísticas e dados do dashboard"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
