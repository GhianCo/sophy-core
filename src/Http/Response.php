<?php

namespace Sophy\Http;

use JsonSerializable;
use Sophy\Http\HttpErrorCode;
use Sophy\View\View;

class Response implements JsonSerializable
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
     * Response HTTP headers.
     *
     * @var array
     */
    protected array $headers = [];
    /**
     * Response content.
     *
     * @var string|null
     */
    protected ?string $content = null;

    /**
     * @var object|null
     */
    private $pagination;

    private $error;

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
     * Set HTTP header `$header` to `$value`.
     *
     * @param string $header
     * @param string $value
     * @return self
     */
    public function setHeader(string $header, string $value): self {
        $this->headers[strtolower($header)] = $value;
        return $this;
    }

    /**
     * Set the `"Content-Type"` header for this response.
     *
     * @param string $value
     * @return self
     */
    public function setContentType(string $value): self {
        $this->setHeader("Content-Type", $value);
        return $this;
    }

    /**
     * Set the response content.
     *
     * @param string $content
     * @return self
     */
    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
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

    public static function json(array $data): self {
        return (new self())
            ->setContentType("application/json")
            ->setContent(json_encode($data));
    }

    /**
     * Create a new plain text response.
     *
     * @param string $text
     * @return self
     */
    public static function text(string $text): self {
        return (new self())
            ->setContentType("text/plain")
            ->setContent($text);
    }

    public static function view(string $view, array $params = [], string $layout = null): self {
        $content = app(View::class)->render($view, $params, $layout);

        return (new self())
            ->setContentType("text/html")
            ->setContent($content);
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
        } elseif ($this->error !== null) {
            $payload['error'] = $this->error;
        }

        if ($this->pagination !== null) {
            $payload['pagination'] = $this->pagination;
        }

        return $payload;
    }
}
