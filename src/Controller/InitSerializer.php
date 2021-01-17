<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class InitSerializer {

	protected $serializer;

	public function __construct()
	{
		$encoders = [new XmlEncoder(), new JsonEncoder()];
		$normalizers = [new ObjectNormalizer()];

		$this->serializer = new Serializer($normalizers, $encoders);
	}

	/**
	 * Returns a JSON response
	 *
	 * @param array|string $data
	 * @param int $status
	 * @param array $headers
	 * @param bool $serialize Need serializing 2nd
	 * @param bool $url Escape slashes 1st
	 * @param bool $toArray Init objects toArray before serialize
	 * @return JsonResponse|Response
	 */
	public function response($data, $status = 200, $headers = [], $serialize = false, $url = false, $toArray = false)
	{
		if ($toArray) {
			if (is_array($data)) {
				$tempData = [];
				foreach ($data as $dat) {
					$tempData[] = $dat->toArray();
				}
				$data = $tempData;
			} else {
				$data = $data->toArray();
			}
		}
		if ($url) {
			return new Response(json_encode($data, JSON_UNESCAPED_SLASHES), $status, $headers);
		} elseif ($serialize) {
			return new Response($this->serializer->serialize($data, 'json'), $status, $headers);
		}
		return new JsonResponse($data, $status, $headers);
	}

	protected function transformJsonBody(Request $request)
	{
		$data = json_decode($request->getContent(), true);

		if ($data === null) {
			return $request;
		}

		$request->request->replace($data);

		return $request;
	}
}