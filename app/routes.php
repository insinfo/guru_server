<?php
/*declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group( "/users", function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};*/

declare(strict_types=1);

use Slim\App;
use Slim\Psr7\Request;
use Slim\Psr7\Response;


// Note these extra use statements:
use Tqdev\PhpCrudApi\Api;
use Tqdev\PhpCrudApi\Config;
use Psr\Http\Message\ResponseInterface;

return function (App $app) {


    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $container = $app->getContainer();

    // Add this handler for PHP-CRUD-API:
    $app->any('/api[/{params:.*}]', function (
        Request $request,
        Response $response,
        array $args
    ) use ($container) {

        $config = new Config([
            'username' => 'tastyt11_app',
            'password' => '@Ins257257',
            'database' => 'tastyt11_guru',
            'basePath' => '/api',
            "driver" => "mysql",
            "port" => "3306",
            "address" => "localhost",
            'middlewares' => 'customization',//Response
            'customization.afterHandler' => function ($operation, $tableName, ResponseInterface $response, $environment) {

                if ($tableName == 'questions' && $operation == 'list') {

                    $body = $response->getBody();
                    $contents = $body->getContents();
                    $data = json_decode($contents, true);

                    if (is_array($data)) {
                        if (array_key_exists('records', $data)) {
                            if (is_array($data['records'])) {
                                foreach ($data['records'] as &$item) {
                                    $item["incorrectAnswers"] = json_decode($item["incorrectAnswers"], true);

                                    $item["categoryName"] = $item["category"]["name"];
                                }
                            }
                        }
                    }

                    $body = \Nyholm\Psr7\Stream::create();
                    $body->write(json_encode($data));


                    $response = $response->withBody($body);
                    $response = $response->withHeader("Content-Type","application/json; charset=utf-8");

                    //Content-Type: text/html; charset=utf-8.
                }
                return $response;
            },
        ]);
        $api = new Api($config);
        $response = $api->handle($request);
        return $response;
    });
};
