<?php

namespace Detrack\DetrackWoocommerce;

/**
 * This class encapsulates NULL (yes, wtf) due to the limitations of the ExpressionLanguage engine.
 *
 * In the case of expressions such as order.foo.bar where foo is a property whose existence cannot be guaranteed all the time,
 * this DummyNull class protects against "trying to get property of null object" errors.
 * This class should be returned in other Dummy classes when the key that was being retrieved does not exist, instead of the regular NULL.
 */
class DummyNull
{
    /**
     * Getter function to fool the ExpressionLanguage Engine that everything is okay.
     * Always returns another instance of itself, to prevent "trying to get property of null object errors".
     *
     * @return DummyNull another instance of itself
     */
    public function __get($key)
    {
        return new self();
    }

    /**
     * toString magic method to fool the ExpressionLanguage Engine that this object is actually a blank string.
     * Always returns an empty string, to appear in the console and in formatted data as nothing.
     *
     * @return string an empty string
     */
    public function __toString()
    {
        return '';
    }
}
