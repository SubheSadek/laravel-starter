<?php

declare(strict_types=1);

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

/**
 * Returns a custom response with success status and data.
 */
function withSuccess(mixed $data = new stdClass, string $message = '', int $status = 200): Response
{
    return customResponse($data, true, $status, $message);
}

/**
 * Returns a custom response with success status and data.
 */
function withSuccessResourceList(ResourceCollection $data, string $message = '', int $status = 200): Response
{
    return customResponse($data->response()->getData(), true, $status, $message);
}

/**
 * Returns a custom response with error status and data.
 */
function withError(string $message, int $status = 400, mixed $data = new stdClass): Response
{
    return customResponse($data, false, $status, $message);
}

/**
 * Returns a custom response with validation error status and data.
 */
function withValidationError(object $message = new stdClass, mixed $data = new stdClass): Response
{
    return response([
        'json_data' => $data,
        'success' => false,
        'status' => 422,
        'messages' => (object) $message,
    ], 422);
}

/**
 * Returns a custom response with the given data, success status, status code, and message.
 */
function customResponse(mixed $data, bool $success, int $status, string $message): Response
{
    return response([
        'json_data' => $data ?? new stdClass,
        'success' => (bool) $success,
        'status' => (int) $status,
        'message' => (string) $message,
    ], $status);
}
