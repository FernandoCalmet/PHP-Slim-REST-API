<?php

declare(strict_types=1);

namespace App\Controller\Task;

use Slim\Http\Request;
use Slim\Http\Response;

final class GetAll extends Base
{
    public function __invoke(Request $request, Response $response): Response
    {
        $input = (array) $request->getParsedBody();
        $userId = $this->getAndValidateUserId($input);

        $tasks = $this->getServiceFindTask()->getAllByUser($userId);

        return $this->jsonResponse($response, 'success', $tasks, 200);
    }
}
