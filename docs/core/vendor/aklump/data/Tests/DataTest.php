<?php
namespace AKlump\Data;

/**
 * Class DataTest
 * @package AKlump\Data
 */
class DataTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidTestThrows()
    {
        $subject = (object) array('do' => null);
        $this->data->fill($subject, 'do', 're', 'bogus');
    }

    public function testFillWithObject()
    {
        $subject = (object) array('do' => null);
        $return = $this->data->fill($subject, 'do', 're', 'is_null');
        $this->assertInstanceOf('AKlump\Data\DataInterface', $return);

        $this->assertSame('re', $subject->do);

        $subject = (object) array();
        $this->data->fill($subject, 'do', 're', 'not_exists');
        $this->assertSame('re', $subject->do);

        $subject = (object) array('do' => '');
        $this->data->fill($subject, 'do', 're', 'not_exists');
        $this->assertSame('', $subject->do);
    }

    /**
     * Provides data for testEmpty.
     */
    function DataForTestFillProvider()
    {
        $tests = array();
        $tests[] = array(
            'href',
            array('href' => 'hat'),
            4,
            array('href' => 'hat'),
            function ($current, $exists, &$value) {
                if ($exists && is_numeric($current)) {
                    $value *= 2;

                    return true;
                }

                return false;
            },
        );
        $tests[] = array(
            'href',
            array('href' => 5),
            4,
            array('href' => 8),
            function ($current, $exists, &$value) {
                if ($exists && is_numeric($current)) {
                    $value *= 2;

                    return true;
                }

                return false;
            },
        );
        $tests[] = array(
            'href',
            array('href' => null),
            'javascript:void(0)',
            array('href' => null),
            'strict',
        );
        $tests[] = array(
            'href',
            array('href' => ''),
            'javascript:void(0)',
            array('href' => 'javascript:void(0)'),
            'strict',
        );
        $tests[] = array(
            'href',
            array('href' => null),
            'javascript:void(0)',
            array('href' => 'javascript:void(0)'),
            'is_null',
        );
        $tests[] = array(
            'href',
            array('href' => ''),
            'javascript:void(0)',
            array('href' => ''),
            'is_null',
        );
        $tests[] = array(
            'href',
            array(),
            'javascript:void(0)',
            array('href' => 'javascript:void(0)'),
            'not_exists',
        );
        $tests[] = array(
            'href',
            array('href' => null),
            'javascript:void(0)',
            array('href' => null),
            'not_exists',
        );
        $tests[] = array(
            'href',
            array('href' => null),
            'javascript:void(0)',
            array('href' => null),
            // Only replace if it doesn't exist.
            function ($current, $replace, $exists) {
                return !$exists;
            },
        );
        $tests[] = array(
            'do.re.mi',
            array(),
            'javascript:void(0)',
            array('do' => array('re' => array('mi' => 'javascript:void(0)'))),
            null,
        );
        $tests[] = array(
            'href',
            array('href' => null),
            'javascript:void(0)',
            array('href' => 'javascript:void(0)'),
            'empty',
        );
        $tests[] = array(
            'href',
            array(),
            'javascript:void(0)',
            array('href' => 'javascript:void(0)'),
            null,
        );
        $tests[] = array(
            'href',
            array('href' => false),
            'javascript:void(0)',
            array('href' => 'javascript:void(0)'),
            function ($current, $replace) {
                return empty($current);
            },
        );
        $tests[] = array(
            'href',
            array('href' => ''),
            'javascript:void(0)',
            array('href' => 'javascript:void(0)'),
            null,
        );
        $tests[] = array(
            'href',
            array('href' => '/'),
            'javascript:void(0)',
            array('href' => '/'),
            null,
        );

        return $tests;
    }

    /**
     * @dataProvider DataForTestFillProvider
     */
    public function testFill($path, $subject, $value, $control, $test)
    {
        $return = $this->data->fill($subject, $path, $value, $test);
        $this->assertSame((array) $control, (array) $subject);
        $this->assertInstanceOf('AKlump\Data\DataInterface', $return);
    }

    /**
     * Provides data for testEnsure.
     */
    function DataForTestEnsureProvider()
    {
        // path, existing, default value, control
        $tests = array();
        $tests[] = array(
            'page.page_top',
            array(),
            array(),
            array('page' => array('page_top' => array())),
        );
        $tests[] = array(
            'do',
            array('do' => 're'),
            'mi',
            array('do' => 're'),
        );
        $tests[] = array(
            'do',
            array(),
            'mi',
            array('do' => 'mi'),
        );
        $tests[] = array(
            'do',
            array('do' => ''),
            'mi',
            array('do' => ''),
        );
        $tests[] = array(
            'do.re',
            array('do' => array()),
            'mi',
            array('do' => array('re' => 'mi')),
        );
        $tests[] = array(
            'do',
            (object) array('do' => ''),
            'mi',
            (object) array('do' => ''),
        );

        return $tests;
    }

    /**
     * @dataProvider DataForTestEnsureProvider
     */
    public function testEnsure($path, $subject, $default, $control)
    {
        $return = $this->data->ensure($subject, $path, $default);
        $this->assertSame((array) $control, (array) $subject);
        $this->assertInstanceOf('AKlump\Data\DataInterface', $return);
    }

    public function testSetObjectWithArrayTemplates()
    {
        $object = new \stdClass;
        $this->data->set($object, 'do.re.mi', 'fa', array());
        $this->assertInternalType('array', $object->do);
        $this->assertInternalType('array', $object->do['re']);
        $this->assertInternalType('string', $object->do['re']['mi']);
    }

    public function testSetObject()
    {
        $object = new \stdClass;
        $result = $this->data->set($object, 'do.re.mi', 'fa');
        $this->assertSame($this->data, $result);
        $this->assertSame('fa', $object->do->re->mi);
    }

    public function testSetArrayWithTemplate()
    {
        $array = array();
        $result = $this->data->set($array, 'do.re.mi', 'fa', array('#type' => 'value'));
        $this->assertSame($this->data, $result);
        $this->assertSame(array(
            '#type' => 'value',
            'mi'    => 'fa',
        ), $array['do']['re']);
        $this->assertSame('fa', $array['do']['re']['mi']);
    }

    public function testSetArray()
    {
        $array = array();
        $result = $this->data->set($array, 'do.re.mi', 'fa');
        $this->assertSame($this->data, $result);
        $this->assertSame('fa', $array['do']['re']['mi']);
    }

    public function testCallbackFiredWhenSubjectIsEmpty()
    {
        $called = false;
        $this->data->get(null, 'do.re.mi', 'default', function ($value) use (&$called) {
            $called = true;
        });
        $this->assertTrue($called);
    }

    public function testCallbackMultidimensional()
    {
        $value = array('do' => array('re' => array('mi' => 'fa')));
        $this->assertSame('Value is fa', $this->data->get($value, 'do.re.mi', null, function ($value) {
            return 'Value is ' . $value;
        }));
    }

    public function testCallback()
    {
        $value = array(9, 6);
        $callback = function ($value, $defaultValue) {
            return $value === $defaultValue ? $value : 2 * $value;
        };
        $this->assertSame(18, $this->data->get($value, 0, 5, $callback));
        $this->assertSame(12, $this->data->get($value, 1, 5, $callback));
        $this->assertSame(5, $this->data->get($value, 2, 5, $callback));
    }

    public function testEmptySubjectReturnsDefault()
    {
        $this->assertSame('pepperoni', $this->data->get(array(), null, 'pepperoni'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPathAsNullThrows()
    {
        $this->data->get(array('do'), (object) array('1', '2'));
    }

    public function testPathAsIntWorks()
    {
        $subject = array(1 => array(1 => 'pizza'));
        $this->assertSame('pizza', $this->data->get($subject, 1.1));
    }

    /**
     * Provides data for testGet.
     */
    function DataForTestGetProvider()
    {
        // $subject, $path, $default, $control
        $tests = array();
        $tests[] = array(
            array('alpha' => array('bravo' => array('charlie' => array('delta' => array('echo' => array('foxtrot' => 'golf')))))),
            'alpha.bravo.charlie.delta.echo.foxtrot',
            null,
            'golf',
        );
        $tests[] = array(
            array('do' => array('alpha' => 'bravo')),
            'do.delta',
            null,
            null,
        );
        $tests[] = array(
            array('do' => array('alpha' => 'bravo')),
            'do.alpha',
            'charlie',
            'bravo',
        );
        $tests[] = array(
            array('do' => array('alpha' => 'bravo')),
            'do.delta',
            'charlie',
            'charlie',
        );
        $tests[] = array(
            array('do' => 're'),
            'do',
            'mi',
            're',
        );
        $tests[] = array(
            array('do' => 're'),
            'fa',
            'mi',
            'mi',
        );

        return $tests;
    }

    /**
     * @dataProvider DataForTestGetProvider
     */
    public function testGetArraysSimpleObjects($subject, $path, $default, $control)
    {
        $this->assertSame($this->data->get($subject, $path, $default), $control);
        $this->assertSame($this->data->get($subject, explode('.', $path), $default), $control);

        // Convert the top array to a \stdClass
        $object = (object) $subject;
        $this->assertSame($this->data->get($object, $path, $default), $control);

        // Now let's make an object of objects using json.
        $object2 = json_decode(json_encode($subject));
        $this->assertSame($this->data->get($object2, $path, $default), $control);
    }

    public function testObjectWithGetMethod()
    {
        // https://phpunit.de/manual/current/en/test-doubles.html
        $object3 = $this->getMockBuilder('ClassWithGet')
                        ->setMethods(array('get'))
                        ->getMock();
        $object3->expects($this->any())
                ->method('get')
                ->will($this->returnValue('do'));
        $this->assertSame($this->data->get($object3, 'do'), 'do');
    }

    public function setUp()
    {
        $this->data = new Data();
    }
}

