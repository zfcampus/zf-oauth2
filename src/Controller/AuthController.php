<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Controller;

use OAuth2\Request as OAuth2Request;
use OAuth2\Response as OAuth2Response;
use OAuth2\Server as OAuth2Server;
use Zend\Http\PhpEnvironment\Request as PhpEnvironmentRequest;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\ContentNegotiation\ViewModel;

class AuthController extends AbstractActionController
{
    /**
     * @var OAuth2Server
     */
    protected $server;

    /**
     * @var boolean
     */
    protected $apiProblemErrorResponse = true;

    /**
     * Constructor
     *
     * @param $server OAuth2Server
     */
    public function __construct(OAuth2Server $server)
    {
        $this->server = $server;
    }

    /**
     * @return boolean
     */
    public function isApiProblemErrorResponse()
    {
        return $this->apiProblemErrorResponse;
    }

    /**
     * @param boolean $apiProblemErrorResponse
     */
    public function setApiProblemErrorResponse($apiProblemErrorResponse)
    {
        $this->apiProblemErrorResponse = $apiProblemErrorResponse;
    }

    /**
     * Token Action (/oauth)
     */
    public function tokenAction()
    {
        $request = $this->getRequest();
        if (! $request instanceof HttpRequest) {
            // not an HTTP request; nothing left to do
            return;
        }

        if ($request->isOptions()) {
            // OPTIONS request.
            // This is most likely a CORS attempt; as such, pass the response on.
            return $this->getResponse();
        }

        $oauth2request = $this->getOAuth2Request();
        $response = $this->server->handleTokenRequest($oauth2request);

        if ($response->isClientError()) {
            return $this->getErrorResponse($response);
        }

        return $this->setHttpResponse($response);
    }

    /**
     * Test resource (/oauth/resource)
     */
    public function resourceAction()
    {
        // Handle a request for an OAuth2.0 Access Token and send the response to the client
        if (!$this->server->verifyResourceRequest($this->getOAuth2Request())) {
            $response   = $this->server->getResponse();
            return $this->getApiProblemResponse($response);
        }

        $httpResponse = $this->getResponse();
        $httpResponse->setStatusCode(200);
        $httpResponse->getHeaders()->addHeaders(array('Content-type' => 'application/json'));
        $httpResponse->setContent(
            json_encode(array('success' => true, 'message' => 'You accessed my APIs!'))
        );
        return $httpResponse;
    }

    /**
     * Authorize action (/oauth/authorize)
     */
    public function authorizeAction()
    {
        $request  = $this->getOAuth2Request();
        $response = new OAuth2Response();

        // validate the authorize request
        $isValid = $this->server->validateAuthorizeRequest($request, $response);

        if (!$isValid) {
            return $this->getErrorResponse($response);
        }

        $authorized = $request->request('authorized', false);
        if (empty($authorized)) {
            $clientId = $request->query('client_id', false);
            $view = new ViewModel(array('clientId' => $clientId));
            $view->setTemplate('oauth/authorize');
            return $view;
        }

        $is_authorized = ($authorized === 'yes');
        $this->server->handleAuthorizeRequest(
            $request,
            $response,
            $is_authorized,
            $this->getRequest()->getQuery('user_id', null)
        );

        $redirect = $response->getHttpHeader('Location');
        if (! empty($redirect)) {
            return $this->redirect()->toUrl($redirect);
        }

        return $this->getErrorResponse($response);
    }

    /**
     * Receive code action prints the code/token access
     */
    public function receiveCodeAction()
    {
        $code = $this->params()->fromQuery('code', false);
        $view = new ViewModel(array(
            'code' => $code
        ));
        $view->setTemplate('oauth/receive-code');
        return $view;
    }

    /**
     * @param OAuth2Response $response
     * @return \ZF\ApiProblem\ApiProblemResponse|\Zend\Stdlib\ResponseInterface
     */
    protected function getErrorResponse(OAuth2Response $response)
    {
        if ($this->isApiProblemErrorResponse()) {
            return $this->getApiProblemResponse($response);
        } else {
            return $this->setHttpResponse($response);
        }
    }

    /**
     * Map OAuth2Response to ApiProblemResponse
     *
     * @param OAuth2Response $response
     * @return ApiProblemResponse
     */
    protected function getApiProblemResponse(OAuth2Response $response)
    {
        $parameters       = $response->getParameters();
        $errorUri         = isset($parameters['error_uri'])         ? $parameters['error_uri']         : null;
        $error            = isset($parameters['error'])             ? $parameters['error']             : null;
        $errorDescription = isset($parameters['error_description']) ? $parameters['error_description'] : null;

        return new ApiProblemResponse(
            new ApiProblem(
                $response->getStatusCode(),
                $errorDescription,
                $errorUri,
                $error
            )
        );
    }

    /**
     * Create an OAuth2 request based on the ZF2 request object
     *
     * Marshals:
     *
     * - query string
     * - body parameters, via content negotiation
     * - "server", specifically the request method and content type
     * - raw content
     * - headers
     *
     * This ensures that JSON requests providing credentials for OAuth2
     * verification/validation can be processed.
     *
     * @return OAuth2Request
     */
    protected function getOAuth2Request()
    {
        $zf2Request = $this->getRequest();
        $headers    = $zf2Request->getHeaders();

        // Marshal content type, so we can seed it into the $_SERVER array
        $contentType = '';
        if ($headers->has('Content-Type')) {
            $contentType = $headers->get('Content-Type')->getFieldValue();
        }

        // Get $_SERVER superglobal
        $server = array();
        if ($zf2Request instanceof PhpEnvironmentRequest) {
            $server = $zf2Request->getServer()->toArray();
        } elseif (!empty($_SERVER)) {
            $server = $_SERVER;
        }
        $server['REQUEST_METHOD'] = $zf2Request->getMethod();

        // Seed headers with HTTP auth information
        $headers = $headers->toArray();
        if (isset($server['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $server['PHP_AUTH_USER'];
        }
        if (isset($server['PHP_AUTH_PW'])) {
            $headers['PHP_AUTH_PW'] = $server['PHP_AUTH_PW'];
        }
        if (isset($server['HTTP_AUTHORIZATION'])) {
            $headers['AUTHORIZATION'] = $server['HTTP_AUTHORIZATION'];
        }

        // Ensure the bodyParams are passed as an array
        $bodyParams = $this->bodyParams() ?: array();

        return new OAuth2Request(
            $zf2Request->getQuery()->toArray(),
            $this->bodyParams(),
            array(), // attributes
            array(), // cookies
            array(), // files
            $server,
            $zf2Request->getContent(),
            $headers
        );
    }

    /**
     * Convert the OAuth2 response to a \Zend\Http\Response
     *
     * @param $response OAuth2Response
     * @return \Zend\Http\Response
     */
    private function setHttpResponse(OAuth2Response $response)
    {
        $httpResponse = $this->getResponse();
        $httpResponse->setStatusCode($response->getStatusCode());

        $headers = $httpResponse->getHeaders();
        $headers->addHeaders($response->getHttpHeaders());
        $headers->addHeaderLine('Content-type', 'application/json');

        $httpResponse->setContent($response->getResponseBody());
        return $httpResponse;
    }
}
