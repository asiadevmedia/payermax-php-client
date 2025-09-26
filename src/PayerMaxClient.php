<?php
namespace TheAngels\PayerMax;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class PayerMaxClient
{
    private Client $httpClient;

    private $baseUrl;
    private $apiKey;
    private $secretKey;

    /**
     * Constructor untuk inisialisasi client.
     *
     * @param string $apiKey Kunci API Anda.
     * @param string $secretKey Kunci Rahasia Anda.
     * @param bool $isProduction Set ke true untuk mode produksi, false untuk UAT/testing.
     */
    public function __construct(private readonly string $apiKey, private readonly string $secretKey, private readonly PayerMaxEnvironment $environment = PayerMaxEnvironment::UAT) {
        // Inisialisasi Guzzle HTTP Client dengan URL dari Enum
        $this->httpClient = new Client([
            'base_uri' => $this->environment->value,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
    }

    /**
     * Menghasilkan signature untuk request.
     * **CATATAN PENTING:** Logika ini adalah CONTOH.
     * Anda HARUS menyesuaikannya dengan formula signature dari dokumentasi PayerMax.
     * Biasanya melibatkan pengurutan parameter, penggabungan string, dan hashing (misal: sha256).
     *
     * @param array $params Parameter yang akan dikirim.
     * @return string Signature yang dihasilkan.
     */
    private function generateSignature(array $params): string
    {
        ksort($params);
        $stringToSign = http_build_query($params);
        $stringToSign .= $this->secretKey;
        return hash('sha256', $stringToSign);
    }

    /**
     * Fungsi utama untuk mengirim request ke API PayerMax.
     *
     * @param string $method Metode HTTP ('POST', 'GET', dll).
     * @param string $endpoint Path endpoint API (misal: '/v2/payment/apply').
     * @param array $data Data atau parameter yang akan dikirim.
     * @return array Respon dari API dalam bentuk array.
     */
    private function sendRequest(string $method, string $endpoint, array $data = []): array
    {
        $data['timestamp'] = time();
        $data['apiKey'] = $this->apiKey;

        $data['signature'] = $this->generateSignature($data);

        try {
            $response = $this->httpClient->request($method, $endpoint, ['json' => $data]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return [
                    'error' => true,
                    'message' => 'API Request Failed',
                    'details' => json_decode($e->getResponse()->getBody()->getContents(), true)
                ];
            }
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * CONTOH: Membuat permintaan pembayaran (Collection Service).
     * **CATATAN:** Nama parameter ('orderId', 'amount', dll) harus disesuaikan.
     *
     * @param array $paymentDetails Detail pembayaran.
     * @return array Respon API.
     */
    public function createPayment(array $paymentDetails): array
    {
        $endpoint = '/v2/payment/apply'; // GANTI ENDPOINT INI
        return $this->sendRequest('POST', $endpoint, $paymentDetails);
    }

    /**
     * CONTOH: Membuat permintaan pencairan dana (Disbursement Service).
     *
     * @param array $disbursementDetails Detail pencairan.
     * @return array Respon API.
     */
    public function createDisbursement(array $disbursementDetails): array
    {
        $endpoint = '/v2/disbursement/apply'; // GANTI ENDPOINT INI
        return $this->sendRequest('POST', $endpoint, $disbursementDetails);
    }
}