<?php

namespace Sophy\Actions;

use JsonSerializable;
use Sophy\Http\HttpErrorCode;

class ActionPayload implements JsonSerializable
{
    private int $code;

    /**
     * @var array|object|null
     */
    private $data;
    /**
     * @var string|null
     */
    private $message;

    /**
     * @var object|null
     */
    private $pagination;

    private HttpErrorCode $error;

    public function __construct(
        int $code = 200,
        $data = null,
        $message = null,
        $pagination = null,
        $error = null
    ) {
        $this->code = $code;
        $this->data = $data;
        $this->message = $message;
        $this->pagination = $pagination;
        $this->error = $error;
    }

    public function getStatusCode(): int
    {
        return $this->code;
    }

    /**
     * @return array|null|object
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string|object
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return null|object
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    public function getError(): ?HttpErrorCode
    {
        return $this->error;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        $payload = [
            'code' => $this->code
        ];

        if ($this->message !== null) {
            $payload['message'] = $this->message;
        }

        if ($this->data !== null) {
            $payload['data'] = $this->data;

            if ($this->pagination !== null) {
                $payload['pagination'] = $this->pagination;
            }
        } elseif ($this->error !== null) {
            $payload['type'] = $this->error->getType();
            $payload['message'] = $this->error->getDescription();
        }

        return $payload;
    }
}
