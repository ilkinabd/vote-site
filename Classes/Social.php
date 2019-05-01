<?php
/**
 * Created by PhpStorm.
 * User: xterminate
 * Date: 10.03.2019
 * Time: 15:20
 */

namespace Classes;

class Social implements ISocial
{
    protected $name;
    protected $url;
    protected $clientId;
    protected $redirectUri;
    protected $responseType;
    protected $code;
    protected $grantType;
    protected $scope;
    protected $clientSecret;

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param mixed $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param mixed $clientId
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return mixed
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @param mixed $redirectUri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * @return mixed
     */
    public function getResponseType()
    {
        return $this->responseType;
    }

    /**
     * @param mixed $responseType
     */
    public function setResponseType($responseType)
    {
        $this->responseType = $responseType;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getGrantType()
    {
        return $this->grantType;
    }

    /**
     * @param mixed $grantType
     */
    public function setGrantType($grantType)
    {
        $this->grantType = $grantType;
    }

    public function generateAuthUrl()
    {
        // TODO: Implement generateAuthUrl() method.
    }

    public function authorize($code)
    {
        // TODO: Implement authorize() method.
    }

    /**
     * @return string
     */
    public function generateTokenUrl()
    {
        // TODO: Implement generateTokenUrl() method.
    }

    /**
     * @return mixed
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param mixed $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @param $token
     * @return array
     */
    public function getUserInfo($token)
    {
        // TODO: Implement getUserInfo() method.
    }
}