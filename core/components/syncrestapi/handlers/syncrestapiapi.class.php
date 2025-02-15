<?php

class syncRestApiApi
{
    /** @var modX */
    protected $modx;

    /** @var syncRestApi */
    protected $syncRestApi;

    /** @var array */
    protected $config = [];

    /**
     * Конструктор класса
     * 
     * @param syncRestApi $syncRestApi
     * @param array       $config
     */
    public function __construct(syncRestApi &$syncRestApi, array $config = [])
    {
        $this->syncRestApi = $syncRestApi;
        $this->modx = $syncRestApi->modx;
        $this->config = $config;
    }

    /**
     * Проверяет, является ли HTTP-код успешным.
     * 
     * @param int $code
     * @return bool
     */
    public function isValidCode(int $code): bool
    {
        return in_array($code, [200, 201, 202], true);
    }

    /**
     * Формирует полный URL API-эндпоинта.
     * 
     * @param string $endpoint
     * @return string
     */
    public function endpoint(string $endpoint): string
    {
        return rtrim($this->syncRestApi->getOption('api_endpoint'), '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * Возвращает заголовки для запроса.
     * 
     * @return array
     */
    protected function headers(): array
    {
        return [
            'Accept: application/json',
            'Authorization: ' . $this->syncRestApi->getRawOption('access_token'),
            'Content-Type: application/json',
        ];
    }

    /**
     * Универсальный метод для выполнения HTTP-запросов.
     * 
     * @param string $method
     * @param string $endpoint
     * @param array  $data
     * @return array
     */
    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->endpoint($endpoint);
        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers());

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'code' => 500, 'error' => $error, 'data' => []];
        }

        curl_close($ch);
        $decodedResponse = json_decode($response, true);

        return [
            'success' => $this->isValidCode($httpCode),
            'code' => $httpCode,
            'data' => $decodedResponse ?? [],
        ];
    }

    /**
     * Авторизация и получение токена.
     * 
     * @return array
     */
    public function authorization(): array
    {
        $data = ['company_token' => $this->syncRestApi->getOption('company_token')];

        $response = $this->request('GET', '/get_token', $data);

        if ($response['success'] && isset($response['data']['token'])) {
            $this->syncRestApi->updateSetting('access_token', $response['data']['token']);
        }

        return $response;
    }

    /**
     * Выполняет GET-запрос.
     * 
     * @param string $endpoint
     * @param array  $data
     * @return array
     */
    public function get(string $endpoint, array $data = []): array
    {
        return $this->request('GET', $endpoint, $data);
    }

    /**
     * Выполняет POST-запрос.
     * 
     * @param string $endpoint
     * @param array  $data
     * @return array
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, $data);
    }

    /**
     * Получает категории.
     * 
     * @param array $data
     * @return array
     */
    public function getCategories(array $data = []): array
    {
        return $this->get('/categories', $data);
    }

    /**
     * Получает товары.
     * 
     * @param array $data
     * @return array
     */
    public function getProducts(array $data = []): array
    {
        return $this->get('/products', $data);
    }

    /**
     * Создает заказ.
     * 
     * @param array $data
     * @return array
     */
    public function createOrder(array $data = []): array
    {
        return $this->post('/create_order', $data);
    }
}