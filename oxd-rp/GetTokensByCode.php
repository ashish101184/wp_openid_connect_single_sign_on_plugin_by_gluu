<?php
/**
 * Created Vlad Karapetyan
 */

require_once 'ClientOXDRP.php';

class GetTokensByCode extends ClientOXDRP
{
    /**start parameter for request!**/
    private $request_oxd_id = null;
    private $request_code = null;
    private $request_state = null;
    private $request_nonce  = null;
    /**end request parameter**/

    /**start parameter for response!**/
    private $response_access_token;
    private $response_expires_in;
    private $response_id_token;
    private $response_id_token_claims;
    /**end response parameter**/

    public function __construct()
    {
        parent::__construct(); // TODO: Change the autogenerated stub
    }


    /**
     * @return mixed
     */
    public function getRequestOxdId()
    {
        return $this->request_oxd_id;
    }

    /**
     * @return null
     */
    public function getRequestNonce()
    {
        return $this->request_nonce;
    }

    /**
     * @param null $request_nonce
     */
    public function setRequestNonce($request_nonce)
    {
        $this->request_nonce = $request_nonce;
    }

    /**
     * @param mixed $request_oxd_id
     */
    public function setRequestOxdId($request_oxd_id)
    {
        $this->request_oxd_id = $request_oxd_id;
    }

    /**
     * @return null
     */
    public function getRequestState()
    {
        return $this->request_state;
    }

    /**
     * @param null $request_state
     */
    public function setRequestState($request_state)
    {
        $this->request_state = $request_state;
    }

    /**
     * @return null
     */
    public function getRequestCode()
    {
        return $this->request_code;
    }

    /**
     * @param null $request_code
     */
    public function setRequestCode($request_code)
    {
        $this->request_code = $request_code;
    }

    /**
     * @return mixed
     */
    public function getResponseAccessToken()
    {
        $this->response_access_token = $this->getResponseData()->access_token;
        return $this->response_access_token;
    }



    /**
     * @return mixed
     */
    public function getResponseIdToken()
    {
        $this->response_id_token = $this->getResponseData()->id_token;
        return $this->response_id_token;
    }

    /**
     * @return mixed
     */
    public function getResponseIdTokenClaims()
    {
        $this->response_id_token_claims = $this->getResponseData()->id_token_claims;
        return $this->response_id_token_claims;
    }

    public function setCommand()
    {
        $this->command = 'get_tokens_by_code';
    }

    public function setParams()
    {
        $this->params = array(
            "oxd_id" => $this->getRequestOxdId(),
            "code" => $this->getRequestCode(),
            "state" => $this->getRequestState(),
            //"nonce" => $this->getRequestNonce(),
        );
    }

}