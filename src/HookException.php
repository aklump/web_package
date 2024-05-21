<?php

namespace AKlump\WebPackage;

/**
 * Indicate a hook had a problem, but the build shouldn't fail.
 *
 * @deprecated Since version 4.0.0, Use any exception with an exception code N < 255
 * to stop the build. To indicate the hook was skipped throw any exception with
 * a code of 255.
 */
class HookException extends \Exception {

}
