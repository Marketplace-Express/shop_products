<?php
/**
 * User: Wajdi Jurry
 * Date: ١٢‏/٤‏/٢٠٢٠
 * Time: ٢:٤٠ ص
 */

namespace app\common\enums;


use Phalcon\Http\Response\StatusCode;

class HttpStatusesEnum
{
    const OK = StatusCode::OK;
    const NO_CONTENT = StatusCode::NO_CONTENT;
    const BAD_REQUEST = StatusCode::BAD_REQUEST;
    const ACCEPTED = StatusCode::ACCEPTED;
    const UNAUTHORIZED = StatusCode::BAD_UNAUTHORIZED;
    const UNAVAILABLE = StatusCode::SERVICE_UNAVAILABLE;

    /**
     * @return array
     */
    static public function getValues(): array
    {
        return [
            self::OK,
            self::NO_CONTENT,
            self::BAD_REQUEST,
            self::ACCEPTED,
            self::UNAUTHORIZED,
            self::UNAVAILABLE
        ];
    }
}