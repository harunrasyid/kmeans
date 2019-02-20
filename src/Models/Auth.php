<?php 
namespace Permengandum\Kmeans\Models;

use Medoo\Medoo as Database;
use Permengandum\Kmeans\Libraries\TokenGeneratorService;
use Permengandum\Kmeans\Exceptions;

class Auth extends Model
{
    /** @var TokenGeneratorService $token */
    private $token;

    /** @var Database $db */
    private $db;

    public function __construct(
        Database $db,
        TokenGeneratorService $token
    ) {
        $this->db = $db;
        $this->token = $token;
        $secretKey = config_get('security.jwt_key');
        $this->token->setKey($secretKey);   
    }

    /**
     * Do login action
     * 
     * @param array $credential
     */
    public function login(array $credential)
    {
        $user = $this->validateLogin($credential);
        return $this->createSession($user);
    }

    /**
     * Validate session
     *
     * @param array $session
     * @throws Permengandum\Kmeans\Exceptions\ForbiddenException
     * @return boolean
     */
    public function check($session)
    {
        $userId = array_get($session, 'user_id', '');
        $accessToken = array_get($session, 'access_token', '');
        $refreshToken = array_get($session, 'refresh_token', '');

        $secretKey = config_get('security.jwt_key');
        $this->token->setKey($secretKey);
        
        try {
            $this->token->decode($accessToken);
        } catch (\UnexpectedValueException $e) {
            if ($e instanceof \Firebase\JWT\ExpiredException) {
                try {
                    $this->token->decode($refreshToken);
                } catch (\UnexpectedValueException $e) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }


    /**
     * Validate session and renew if it has been expired
     *
     * @param array $session
     * @throws Permengandum\Kmeans\Exceptions\ForbiddenException
     * @return array
     */
    public function checkAndRenew($session)
    {
        $now = strtotime('now');

        $userId = array_get($session, 'user_id', '');
        $accessToken = array_get($session, 'access_token', '');
        $refreshToken = array_get($session, 'refresh_token', '');

        $secretKey = config_get('security.jwt_key');
        $this->token->setKey($secretKey);
        
        try {
            $this->token->decode($accessToken);
        } catch (\UnexpectedValueException $e) {
            if ($e instanceof \Firebase\JWT\ExpiredException) {
                try {
                    $this->token->decode($refreshToken);
                    $accessToken = $this->generateToken(
                        $now,
                        'Authentication access token',
                        0,
                        7200
                    );
                } catch (\UnexpectedValueException $e) {
                    throw new Exceptions\ForbiddenException();
                }
            } else {
                throw new Exceptions\ForbiddenException();
            }
        }

        return [
            'user_id' => $userId,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }

    /**
     * Generate jwt
     *
     * @param array $user
     * @return array
     */
    private function createSession($user)
    {
        $now = strtotime('now');
        $baseUrl = config_get('base.url');
        $userId = $user['id'];
        $secretKey = config_get('security.jwt_key');
        $this->token->setKey($secretKey);

        $accessToken = $this->generateToken(
            $now,
            'Authentication access token',
            0,
            7200
        );

        $refreshToken = $this->generateToken(
            $now,
            'Authentication refresh token',
            0
        );

        return [
            'user_id' => $userId,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }

    /**
     * Generate token
     *
     * @param integer $now - current timestamp
     * @param string $sub - token subject
     * @param integer $nbf - not before, token will not be valid before this
     * @param integer $exp = expired, token expiraton time
     * @param array $payload - additional information of jwt
     * @return string
     */
    protected function generateToken($now, $sub, $nbf, $exp = 0, $payload = [])
    {
        $baseUrl = config_get('base.url');
        
        $expirationConfig = [];
        if ($exp > 0) {
            $expirationConfig = [
                'exp' => $now + $exp
            ];
        }

        $configurator = array_merge([
            'iss' => $baseUrl,
            'sub' => $sub,
            'aud' => $baseUrl,
            'iat' => $now,
            'nbf' => $now + $nbf,
            'jti' => uniqid(mt_rand(), true),
        ], $expirationConfig, $payload);

        return $this->token->generate($configurator);
    }

    /**
     * Validate login credential
     * @param array $credential
     */
    private function validateLogin(array $credential)
    {
        $username = array_get($credential, 'username', '');
        $password = array_get($credential, 'password', '');
        
        if (strlen($username) === 0) {
            $this->throwUnauthorizedException('Usernamenya ndak bole kosong..');
        }

        if (strlen($password) === 0) {
            $this->throwUnauthorizedException('Passwordnya ndak bole kosong');
        }

        $result = $this->getByUsername(array_get($credential, 'username'));

        if ($result === []) {
            $this->throwUnauthorizedException('Ndak ada username "' . $username . '"');
        }

        if (hash_bcrypt($password) !== $result['password']) {
            $this->throwUnauthorizedException('Passwordnya salaah');
        }

        return $result;
    }

    /**
     * Get user by it's id
     * 
     * @param string $id
     * @return array
     */
    public function getById($id)
    {
        $result =  $this->db->select(
            'user', ['id', 'username', 'password'], [
                'id' => $id,
                'LIMIT' => 1
            ]
        );

        return count($result) > 0 ? $result[0] : [];
    }

    /**
     * Get user by it's username
     * 
     * @param string $username
     * @return array
     */
    public function getByUsername($username)
    {
        $result = $this->db->select(
            'user', ['id', 'username', 'password'], [
                'username' => $username,
                'LIMIT' => 1
            ]
        );

        return count($result) > 0 ? $result[0] : [];
    }

    /**
     * Throw to new unauthorized exception
     *
     * @param string $message - message code (check language resource for detail)
     * @throws Exceptions\UnauthorizedException
     * @return
     */
    private function throwUnauthorizedException($message)
    {
        throw new Exceptions\UnauthorizedException($message);
    }

}