<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 2/14/2019
 * Time: 5:34 PM
 */

namespace Plugin\GHNDelivery\Service;


use Eccube\Common\Constant;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\BaseInfo;
use Eccube\Repository\BaseInfoRepository;
use Plugin\GHNDelivery\Entity\GHNConfig;
use Plugin\GHNDelivery\Entity\GHNWarehouse;
use Plugin\GHNDelivery\Repository\GHNConfigRepository;
use Plugin\GHNDelivery\Repository\GHNDeliveryRepository;
use Plugin\GHNDelivery\Repository\GHNPrefRepository;
use Plugin\GHNDelivery\Repository\GHNWarehouseRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ApiService
{
    /**
     * @var GHNConfigRepository
     */
    protected $configRepo;

    /**
     * @var GHNPrefRepository
     */
    protected $ghnPrefRepo;

    /**
     * @var GHNWarehouseRepository
     */
    protected $ghnWarehouseRepo;

    /**
     * @var GHNDeliveryRepository
     */
    protected $ghnDeliveryRepo;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var BaseInfo
     */
    protected $baseInfo;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * ApiService constructor.
     * @param GHNConfigRepository $configRepo
     * @param GHNPrefRepository $ghnPrefRepo
     * @param GHNWarehouseRepository $ghnWarehouseRepo
     * @param GHNDeliveryRepository $ghnDeliveryRepo
     * @param EccubeConfig $eccubeConfig
     * @param BaseInfo $baseInfo
     */
    public function __construct(GHNConfigRepository $configRepo, GHNPrefRepository $ghnPrefRepo, GHNWarehouseRepository $ghnWarehouseRepo, GHNDeliveryRepository $ghnDeliveryRepo, EccubeConfig $eccubeConfig, BaseInfoRepository $baseInfoRepository, RequestStack $requestStack)
    {
        $this->configRepo = $configRepo;
        $this->ghnPrefRepo = $ghnPrefRepo;
        $this->ghnWarehouseRepo = $ghnWarehouseRepo;
        $this->ghnDeliveryRepo = $ghnDeliveryRepo;
        $this->eccubeConfig = $eccubeConfig;
        $this->baseInfo = $baseInfoRepository->get();
        $this->requestStack = $requestStack;

        $this->endpoint = $eccubeConfig->get('ghn_endpoint');
    }

    /**
     * @param GHNWarehouse $GHNWarehouse
     * @return ApiParserService|mixed
     */
    public function addWarehouse(GHNWarehouse $GHNWarehouse)
    {
        /** @var GHNConfig $config */
        $config = $this->configRepo->find(1);
        if (!$config) {
            return false;
        }
        $url = $this->endpoint . '/AddHubs';

        $data = $GHNWarehouse->getApiCreateParameter();
        $data['token'] = $config->getToken();

        $jsonRet = $this->requestApi($url, $data, true);
        $parser = new ApiParserService();
        $parser->parse($jsonRet);

        return $parser;
    }

    /**
     * @param GHNWarehouse $GHNWarehouse
     * @return ApiParserService|mixed
     */
    public function updateWarehouse(GHNWarehouse $GHNWarehouse)
    {
        /** @var GHNConfig $config */
        $config = $this->configRepo->find(1);
        if (!$config) {
            return false;
        }

        $url = $this->endpoint . '/UpdateHubs';

        $data = $GHNWarehouse->getApiCreateParameter();
        $data['token'] = $config->getToken();
        $data['HubID'] = (int) $GHNWarehouse->getHubId();

        $jsonRet = $this->requestApi($url, $data, true);
        $parser = new ApiParserService();
        $parser->parse($jsonRet);

        return $parser;
    }

    public function findAvailableServices($fromDistrictId, $toDistrictId, $options = array())
    {
        /** @var GHNConfig $config */
        $config = $this->configRepo->find(1);
        if (!$config) {
            return false;
        }

        $url = $this->endpoint . '/FindAvailableServices';

        $data['token'] = $config->getToken();
        $data['FromDistrictID'] = (int) $fromDistrictId;
        $data['ToDistrictID'] = (int) $toDistrictId;
        if (count($options)) {
            foreach ($options as $key => $option) {
                $data[$key] = $option;
            }
        }

        $jsonRet = $this->requestApi($url, $data, true);
        $parser = new ApiParserService();
        $parser->parse($jsonRet);

        return $parser;
    }

    public function createOrder(array $data)
    {
        /** @var GHNConfig $config */
        $config = $this->configRepo->find(1);
        if (!$config) {
            return false;
        }

        $url = $this->endpoint . '/CreateOrder';

        $jsonRet = $this->requestApi($url, $data, true);
        $parser = new ApiParserService();
        $parser->parse($jsonRet);

        return $parser;
    }

    public function updateOrder(array $data)
    {
        /** @var GHNConfig $config */
        $config = $this->configRepo->find(1);
        if (!$config) {
            return false;
        }

        $url = $this->endpoint . '/UpdateOrder';

        $jsonRet = $this->requestApi($url, $data, true);
        $parser = new ApiParserService();
        $parser->parse($jsonRet);

        return $parser;
    }

    /**
     * API request processing
     *
     * @param $url
     * @param array $data
     * @param bool $post
     * @return mixed|string
     */
    public function requestApi($url, $data = [], $post = false)
    {
        log_info('GHN plugin call API: ' . $url);
        if ($post === false && count($data) > 0) {
            $url .= '?'.http_build_query($data);
        }

        $curl = curl_init($url);

        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);

            if (count($data) > 0) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $baseUrl = null;
        if ($this->requestStack->getCurrentRequest()) {
            $baseUrl = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost().$this->requestStack->getCurrentRequest()->getBasePath();
        }

        // Option array
        $options = [
            // HEADER
            CURLOPT_HTTPHEADER => [
                'X-ECCUBE-URL: '.$baseUrl,
                'X-ECCUBE-VERSION: '.Constant::VERSION,
                // ghn require
                'Accept: application/json',
                'Content-Type: application/json',
                'cache-control: no-cache'
            ],
            CURLOPT_HTTPGET => $post === false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => false,
            CURLOPT_CAINFO => \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath(),
            CURLOPT_TIMEOUT_MS => 10000,
        ];

        // Set option value
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        $message = curl_error($curl);
        $info['message'] = $message;
        curl_close($curl);
        log_info('GHN plugin info: ', $info);

//        if ($info['http_code'] !== 200) {
//            throw new NotFoundResourceException();
//        }

        return $result ? $result : $message;
    }
}