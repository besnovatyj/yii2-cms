<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace rest\controllers;

use Besnovatyj\Oauth2\AuthorizationServer;
use Besnovatyj\Oauth2\bridge\Psr7Factory;
use Nyholm\Psr7\Response as Psr7Response;
use Yii;
use yii\rest\Controller;

/**
 * OAuth2 Controller for handling token requests
 */
class OAuth2Controller extends Controller
{
    /**
     * @var AuthorizationServer
     */
    private AuthorizationServer $authServer;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        $this->authServer = Yii::$app->get('oauth2AuthServer');
    }

    /**
     * Access token endpoint
     * POST /oauth2/token
     */
    public function actionToken(): void
    {
        try {
            // Log request parameters
            $bodyParams = Yii::$app->request->getBodyParams();
            Yii::info('OAuth2 token request: ' . json_encode($bodyParams), __METHOD__);

            $psrRequest = Psr7Factory::createServerRequest(Yii::$app->request);
            $psrResponse = new Psr7Response();

            $psrResponse = $this->authServer->getServer()
                ->respondToAccessTokenRequest($psrRequest, $psrResponse);

            Psr7Factory::populateYiiResponse($psrResponse, Yii::$app->response);
        } catch (\League\OAuth2\Server\Exception\OAuthServerException $e) {
            // OAuth2 specific errors
            Yii::error('OAuth2 error: ' . $e->getMessage(), __METHOD__);
            Yii::$app->response->setStatusCode($e->getHttpStatusCode());
            Yii::$app->response->data = [
                'error' => $e->getErrorType(),
                'error_description' => $e->getMessage(),
                'hint' => $e->getHint(),
            ];
        } catch (\Exception $e) {
            // Generic errors
            Yii::error('OAuth2 token request failed: ' . $e->getMessage(), __METHOD__);
            Yii::$app->response->setStatusCode(500);
            Yii::$app->response->data = [
                'error' => 'server_error',
                'error_description' => YII_DEBUG ? $e->getMessage() : 'An internal error occurred',
            ];
        }
    }
}
