<?php

namespace Exan\StabilityBot;

use Exan\Dhp\Websocket\Objects\Payload;

class AnonymizedLogger
{
    private string $logPath;

    public function __construct()
    {
        $this->logPath = $_ENV['LOGS_PATH'];
    }

    public function handlePayload(Payload $payload)
    {
        if (is_null($payload->t)) {
            return;
        }

        $anonymousPayload = $this->anonymize(json_decode(json_encode($payload->d), true));

        $encodedEventData = json_encode($anonymousPayload);

        $data = $this->getKeyableArray(
            json_decode($encodedEventData, true)
        );

        $key = sha1(json_encode($data));

        $dirName = $this->logPath . '/' . $payload->t;

        if (!file_exists($dirName)) {
            mkdir($dirName);
        }

        $fileName = $dirName . '/' . $key . '.json';

        if (file_exists($fileName)) {
            return;
        }

        file_put_contents($fileName, $encodedEventData);
    }

    protected function isAssocArray(mixed $input): bool
    {
        if (!is_array($input) || $input === []) {
            return false;
        }

        return array_keys($input) !== range(0, count($input) - 1);
    }

    protected function getKeyableArray(array $raw): array
    {
        $keyable = [];

        $rawKeys = array_keys($raw);
        sort($rawKeys);

        foreach ($rawKeys as $key) {
            if (!$this->isAssocArray($raw[$key])) {
                $keyable[] = $this->getItemKey($key, $raw[$key]);

                continue;
            }

            $keyable[$key] = $this->getKeyableArray($raw[$key]);
        }

        return $keyable;
    }

    protected function getItemKey(string $key, mixed $value): string
    {
        return $key . gettype($value);
    }

    protected function anonymize(array $sensitiveArray): array
    {
        $anonArray = [];

        $replacementValues = [
            'string' => '::string::',
        ];

        foreach (array_keys($sensitiveArray) as $key) {
            $type = gettype($sensitiveArray[$key]);
            if (isset($replacementValues[$type])) {
                $anonArray[$key] = $replacementValues[$type];
            } elseif (is_array($sensitiveArray[$key])) {
                $anonArray[$key] = $this->anonymize($sensitiveArray[$key]);
            } else {
                $anonArray[$key] = $sensitiveArray[$key];
            }
        }

        return $anonArray;
    }
}
