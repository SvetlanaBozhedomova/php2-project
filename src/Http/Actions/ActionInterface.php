<?php

namespace GeekBrains\php2\Http\Actions;

use GeekBrains\php2\Http\Request;
use GeekBrains\php2\Http\Response;

interface ActionInterface
{
  public function handle(Request $request): Response;
}