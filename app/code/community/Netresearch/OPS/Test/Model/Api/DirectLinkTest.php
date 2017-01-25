<?php
class Netresearch_OPS_Test_Model_Api_DirectLinkTest extends EcomDev_PHPUnit_Test_Case_Controller
{
    private $_model;

    public function setUp()
    {
        parent::setup();
        $this->_model = Mage::getModel('ops/api_directlink');
    }

    public function testCallInvalidUrl()
    {
        $response = $this->_model->call(array(), 'http://localhost');
        $this->assertInternalType('string', $response);
    }

    public function testType()
    {
        $this->assertInstanceOf('Netresearch_OPS_Model_Api_DirectLink', $this->_model);
    }

    public function testXmlParser()
    {
        $xmlExample =
        '<?xml version="1.0"?>
            <ncresponse
                orderID="1121212"
                PAYID="232"
                PAYIDSUB="0"
                NCSTATUS="5"
                NCERROR="50001111"
                NCERRORPLUS="Some of the data entered is incorrect. Please retry."
                ACCEPTANCE=""
                STATUS="0"
                amount=""
                currency="">
            </ncresponse>';
        $arrParams = $this->_model->getParamArrFromXmlString($xmlExample);
        $this->assertEquals("1121212", $arrParams['orderID']);
        $this->assertEquals("232", $arrParams['PAYID']);
        $this->assertEquals("0", $arrParams['PAYIDSUB']);
        $this->assertEquals("5", $arrParams['NCSTATUS']);
        $this->assertEquals("50001111", $arrParams['NCERROR']);
        $this->assertEquals("Some of the data entered is incorrect. Please retry.", $arrParams['NCERRORPLUS']);
        $this->assertEquals("", $arrParams['ACCEPTANCE']);
        $this->assertEquals("0", $arrParams['STATUS']);
        $this->assertEquals("", $arrParams['amount']);
        $this->assertEquals("", $arrParams['currency']);
    }
    
    public function testXmlParserWithHtmlAnswer()
    {
        $xmlExample =
        '<?xml version="1.0"?>
            <ncresponse
                orderID="1121212"
                PAYID="232"
                PAYIDSUB="0"
                NCSTATUS="5"
                NCERROR="50001111"
                NCERRORPLUS="Some of the data entered is incorrect. Please retry."
                ACCEPTANCE=""
                STATUS="0"
                amount=""
                currency="">
                <HTML_ANSWER>PGZvcm0gbmFtZT0iZG93bmxvYWRmb3JtM0QiIGFjdGlvbj0iaHR0cHM6Ly9zZWN1cmUub2dvbmUuY29tL25jb2wvdGVzdC9UZXN0XzNEX0FDUy5hc3AiIG1ldGhvZD0icG9zdCI+DQo8Tk9TQ1JJUFQ+DQpKYXZhU2NyaXB0IGlzIGN1cnJlbnRseSBkaXNhYmxlZCBvciBpcyBub3Qgc3VwcG9ydGVkIGJ5IHlvdXIgYnJvd3Nlci48YnI+DQpQbGVhc2UgY2xpY2sgb24gdGhlICZxdW90O0NvbnRpbnVlJnF1b3Q7IGJ1dHRvbiB0byBjb250aW51ZSB0aGUgcHJvY2Vzc2luZyBvZiB5b3VyIDMtRCBzZWN1cmUgdHJhbnNhY3Rpb24uPGJyPg0KPGlucHV0IGNsYXNzPSJuY29sIiB0eXBlPSJzdWJtaXQiIHZhbHVlPSJDb250aW51ZSIgaWQ9InN1Ym1pdDEiIG5hbWU9InN1Ym1pdDEiIC8+DQo8L05PU0NSSVBUPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iQ1NSRktFWSIgdmFsdWU9IjA0MzA0MzI4NkE0M0ZDM0YyRDhFMDFCOUM2MzYwRTA1Qzg5NkZEMzYiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJDU1JGVFMiIHZhbHVlPSIyMDExMDkxMjE2MjQ1MCIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9IkNTUkZTUCIgdmFsdWU9Ii9uY29sL3Rlc3Qvb3JkZXJkaXJlY3QuYXNwIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iUGFSZXEiIHZhbHVlPSI8P3htbCB2ZXJzaW9uPSZxdW90OzEuMCZxdW90Oz8+PFRocmVlRFNlY3VyZT48TWVzc2FnZSBpZD0mcXVvdDsxMjMmcXVvdDs+PFBBUmVxPjx2ZXJzaW9uPjEuMDI8L3ZlcnNpb24+PE1lcmNoYW50PjxtZXJJRD5OUk1BR0VOVE8zPC9tZXJJRD48bmFtZT5OZXRyZXNlYXJjaCBHbWJIICZhbXA7YW1wOyBDby5LRzwvbmFtZT48dXJsPmh0dHA6Ly93d3cubmV0cmVzZWFyY2guZGU8L3VybD48L01lcmNoYW50PjxQdXJjaGFzZT48eGlkPjExNjI4OTgzPC94aWQ+PGFtb3VudD4xMjY4Ljc1PC9hbW91bnQ+PHB1cmNoQW1vdW50PjEyNjguNzU8L3B1cmNoQW1vdW50PjxjdXJyZW5jeT5FVVI8L2N1cnJlbmN5PjwvUHVyY2hhc2U+PENIPjxhY2N0SUQ+NDAwMDAwWFhYWFhYMDAwMjwvYWNjdElEPjxleHBpcnk+MDExNjwvZXhwaXJ5PjxzZWxCcmFuZD48L3NlbEJyYW5kPjwvQ0g+PC9QQVJlcT48L01lc3NhZ2U+PC9UaHJlZURTZWN1cmU+DQoiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJUZXJtVXJsIiB2YWx1ZT0iaHR0cHM6Ly9zZWN1cmUub2dvbmUuY29tL25jb2wvdGVzdC9vcmRlcl9BM0RTLmFzcCIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9Ik1EIiB2YWx1ZT0iTUFJTldQVEVTVDAwMDAxMTYyODk4MzAxKjEwNzUyNjIiIC8+DQo8L2Zvcm0+DQo8Zm9ybSBtZXRob2Q9InBvc3QiIGFjdGlvbj0iaHR0cHM6Ly9zZWN1cmUub2dvbmUuY29tL25jb2wvdGVzdC9vcmRlcl9hZ3JlZS5hc3AiIG5hbWU9InVwbG9hZEZvcm0zRCI+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJDU1JGS0VZIiB2YWx1ZT0iMDZGM0MzMUQ2RkI1MzIzODg4NjhFRjlGNTA5RUNGNzlBQzIwRDJGMyIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9IkNTUkZUUyIgdmFsdWU9IjIwMTEwOTEyMTYyNDUwIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iQ1NSRlNQIiB2YWx1ZT0iL25jb2wvdGVzdC9vcmRlcmRpcmVjdC5hc3AiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJicmFuZGluZyIgdmFsdWU9Ik9nb25lIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0icGF5aWQiIHZhbHVlPSIxMTYyODk4MyIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9InN0b3JlYWxpYXMiIHZhbHVlPSIiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJoYXNoX3BhcmFtIiB2YWx1ZT0iOTFBMzA1MjFEMEI0QTA1MEFBRDkzRDM5RDY2RkEyM0Y5OEIzRDQ4RCIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9InhpZF8zRCIgdmFsdWU9IiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9InN0YXR1c18zRCIgdmFsdWU9IlhYIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iZWNpXzNEIiB2YWx1ZT0iNyIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImNhcmRudW1iZXIiIHZhbHVlPSIiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJFY29tX1BheW1lbnRfQ2FyZF9WZXJpZmljYXRpb24iIHZhbHVlPSIqMTA3NTI2MiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9IkNWQ0ZsYWciIHZhbHVlPSIxIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iY2F2dl8zRCIgdmFsdWU9IiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImNhdnZhbGdvcml0aG1fM0QiIHZhbHVlPSIiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJzaWduYXR1cmVPS18zRCIgdmFsdWU9IiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9Imhhc2hfcGFyYW1fM0QiIHZhbHVlPSIwMzAzREZDMkI1OTM0MjZCQTExRkQ5RjJBNkQ0NDk5ODEwN0JGN0YzIiAvPg0KPC9mb3JtPg0KPFNDUklQVCBMQU5HVUFHRT0iSmF2YXNjcmlwdCIgRk9SPSJ3aW5kb3ciIEVWRU5UPSJvbkxvYWQiPg0KdmFyIHBvcHVwV2luOw0KdmFyIHN1Ym1pdHBvcHVwV2luID0gMDsNCg0KZnVuY3Rpb24gTG9hZFBvcHVwKCkgew0KCWlmIChzZWxmLm5hbWUgPT0gbnVsbCkJew0KCQlzZWxmLm5hbWUgPSAib2dvbmVNYWluIjsNCgl9DQoJcG9wdXBXaW4gPSB3aW5kb3cub3BlbignYWJvdXQ6YmxhbmsnLCAncG9wdXBXaW4nLCAnaGVpZ2h0PTQwMCwgd2lkdGg9MzkwLCBzdGF0dXM9eWVzLCBkZXBlbmRlbnQ9bm8sIHNjcm9sbGJhcnM9eWVzLCByZXNpemFibGU9bm8nKTsNCglpZiAocG9wdXBXaW4gIT0gbnVsbCkgew0KCQlpZiAgKCFwb3B1cFdpbiB8fCBwb3B1cFdpbi5jbG9zZWQpIHsNCgkJCXJldHVybiAxOw0KCQl9IGVsc2Ugew0KCQkJaWYgKCFwb3B1cFdpbi5vcGVuZXIgfHwgcG9wdXBXaW4ub3BlbmVyID09IG51bGwpIHsNCgkJCQlwb3B1cFdpbi5vcGVuZXIgPSBzZWxmOw0KCQkJfQ0KCQkJc2VsZi5kb2N1bWVudC5mb3Jtcy5kb3dubG9hZGZvcm0zRC50YXJnZXQgPSAncG9wdXBXaW4nOw0KCQkJaWYgKHN1Ym1pdHBvcHVwV2luID09IDEpIHsNCgkJCQlzZWxmLmRvY3VtZW50LmZvcm1zLmRvd25sb2FkZm9ybTNELnN1Ym1pdCgpOw0KCQkJfQ0KCQkJcG9wdXBXaW4uZm9jdXMoKTsNCgkJCXJldHVybiAwOw0KCQl9DQoJfSBlbHNlIHsNCgkJcmV0dXJuIDE7DQoJfQ0KfQ0KCXNlbGYuZG9jdW1lbnQuZm9ybXMuZG93bmxvYWRmb3JtM0Quc3VibWl0KCk7DQovLy0tPg0KPC9TQ1JJUFQ+DQo=</HTML_ANSWER>
            </ncresponse>';
        $arrParams = $this->_model->getParamArrFromXmlString($xmlExample);
        $this->assertEquals("1121212", $arrParams['orderID']);
        $this->assertEquals("232", $arrParams['PAYID']);
        $this->assertEquals("PGZvcm0gbmFtZT0iZG93bmxvYWRmb3JtM0QiIGFjdGlvbj0iaHR0cHM6Ly9zZWN1cmUub2dvbmUuY29tL25jb2wvdGVzdC9UZXN0XzNEX0FDUy5hc3AiIG1ldGhvZD0icG9zdCI+DQo8Tk9TQ1JJUFQ+DQpKYXZhU2NyaXB0IGlzIGN1cnJlbnRseSBkaXNhYmxlZCBvciBpcyBub3Qgc3VwcG9ydGVkIGJ5IHlvdXIgYnJvd3Nlci48YnI+DQpQbGVhc2UgY2xpY2sgb24gdGhlICZxdW90O0NvbnRpbnVlJnF1b3Q7IGJ1dHRvbiB0byBjb250aW51ZSB0aGUgcHJvY2Vzc2luZyBvZiB5b3VyIDMtRCBzZWN1cmUgdHJhbnNhY3Rpb24uPGJyPg0KPGlucHV0IGNsYXNzPSJuY29sIiB0eXBlPSJzdWJtaXQiIHZhbHVlPSJDb250aW51ZSIgaWQ9InN1Ym1pdDEiIG5hbWU9InN1Ym1pdDEiIC8+DQo8L05PU0NSSVBUPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iQ1NSRktFWSIgdmFsdWU9IjA0MzA0MzI4NkE0M0ZDM0YyRDhFMDFCOUM2MzYwRTA1Qzg5NkZEMzYiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJDU1JGVFMiIHZhbHVlPSIyMDExMDkxMjE2MjQ1MCIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9IkNTUkZTUCIgdmFsdWU9Ii9uY29sL3Rlc3Qvb3JkZXJkaXJlY3QuYXNwIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iUGFSZXEiIHZhbHVlPSI8P3htbCB2ZXJzaW9uPSZxdW90OzEuMCZxdW90Oz8+PFRocmVlRFNlY3VyZT48TWVzc2FnZSBpZD0mcXVvdDsxMjMmcXVvdDs+PFBBUmVxPjx2ZXJzaW9uPjEuMDI8L3ZlcnNpb24+PE1lcmNoYW50PjxtZXJJRD5OUk1BR0VOVE8zPC9tZXJJRD48bmFtZT5OZXRyZXNlYXJjaCBHbWJIICZhbXA7YW1wOyBDby5LRzwvbmFtZT48dXJsPmh0dHA6Ly93d3cubmV0cmVzZWFyY2guZGU8L3VybD48L01lcmNoYW50PjxQdXJjaGFzZT48eGlkPjExNjI4OTgzPC94aWQ+PGFtb3VudD4xMjY4Ljc1PC9hbW91bnQ+PHB1cmNoQW1vdW50PjEyNjguNzU8L3B1cmNoQW1vdW50PjxjdXJyZW5jeT5FVVI8L2N1cnJlbmN5PjwvUHVyY2hhc2U+PENIPjxhY2N0SUQ+NDAwMDAwWFhYWFhYMDAwMjwvYWNjdElEPjxleHBpcnk+MDExNjwvZXhwaXJ5PjxzZWxCcmFuZD48L3NlbEJyYW5kPjwvQ0g+PC9QQVJlcT48L01lc3NhZ2U+PC9UaHJlZURTZWN1cmU+DQoiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJUZXJtVXJsIiB2YWx1ZT0iaHR0cHM6Ly9zZWN1cmUub2dvbmUuY29tL25jb2wvdGVzdC9vcmRlcl9BM0RTLmFzcCIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9Ik1EIiB2YWx1ZT0iTUFJTldQVEVTVDAwMDAxMTYyODk4MzAxKjEwNzUyNjIiIC8+DQo8L2Zvcm0+DQo8Zm9ybSBtZXRob2Q9InBvc3QiIGFjdGlvbj0iaHR0cHM6Ly9zZWN1cmUub2dvbmUuY29tL25jb2wvdGVzdC9vcmRlcl9hZ3JlZS5hc3AiIG5hbWU9InVwbG9hZEZvcm0zRCI+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJDU1JGS0VZIiB2YWx1ZT0iMDZGM0MzMUQ2RkI1MzIzODg4NjhFRjlGNTA5RUNGNzlBQzIwRDJGMyIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9IkNTUkZUUyIgdmFsdWU9IjIwMTEwOTEyMTYyNDUwIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iQ1NSRlNQIiB2YWx1ZT0iL25jb2wvdGVzdC9vcmRlcmRpcmVjdC5hc3AiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJicmFuZGluZyIgdmFsdWU9Ik9nb25lIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0icGF5aWQiIHZhbHVlPSIxMTYyODk4MyIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9InN0b3JlYWxpYXMiIHZhbHVlPSIiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJoYXNoX3BhcmFtIiB2YWx1ZT0iOTFBMzA1MjFEMEI0QTA1MEFBRDkzRDM5RDY2RkEyM0Y5OEIzRDQ4RCIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9InhpZF8zRCIgdmFsdWU9IiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9InN0YXR1c18zRCIgdmFsdWU9IlhYIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iZWNpXzNEIiB2YWx1ZT0iNyIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImNhcmRudW1iZXIiIHZhbHVlPSIiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJFY29tX1BheW1lbnRfQ2FyZF9WZXJpZmljYXRpb24iIHZhbHVlPSIqMTA3NTI2MiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9IkNWQ0ZsYWciIHZhbHVlPSIxIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iY2F2dl8zRCIgdmFsdWU9IiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImNhdnZhbGdvcml0aG1fM0QiIHZhbHVlPSIiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJzaWduYXR1cmVPS18zRCIgdmFsdWU9IiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9Imhhc2hfcGFyYW1fM0QiIHZhbHVlPSIwMzAzREZDMkI1OTM0MjZCQTExRkQ5RjJBNkQ0NDk5ODEwN0JGN0YzIiAvPg0KPC9mb3JtPg0KPFNDUklQVCBMQU5HVUFHRT0iSmF2YXNjcmlwdCIgRk9SPSJ3aW5kb3ciIEVWRU5UPSJvbkxvYWQiPg0KdmFyIHBvcHVwV2luOw0KdmFyIHN1Ym1pdHBvcHVwV2luID0gMDsNCg0KZnVuY3Rpb24gTG9hZFBvcHVwKCkgew0KCWlmIChzZWxmLm5hbWUgPT0gbnVsbCkJew0KCQlzZWxmLm5hbWUgPSAib2dvbmVNYWluIjsNCgl9DQoJcG9wdXBXaW4gPSB3aW5kb3cub3BlbignYWJvdXQ6YmxhbmsnLCAncG9wdXBXaW4nLCAnaGVpZ2h0PTQwMCwgd2lkdGg9MzkwLCBzdGF0dXM9eWVzLCBkZXBlbmRlbnQ9bm8sIHNjcm9sbGJhcnM9eWVzLCByZXNpemFibGU9bm8nKTsNCglpZiAocG9wdXBXaW4gIT0gbnVsbCkgew0KCQlpZiAgKCFwb3B1cFdpbiB8fCBwb3B1cFdpbi5jbG9zZWQpIHsNCgkJCXJldHVybiAxOw0KCQl9IGVsc2Ugew0KCQkJaWYgKCFwb3B1cFdpbi5vcGVuZXIgfHwgcG9wdXBXaW4ub3BlbmVyID09IG51bGwpIHsNCgkJCQlwb3B1cFdpbi5vcGVuZXIgPSBzZWxmOw0KCQkJfQ0KCQkJc2VsZi5kb2N1bWVudC5mb3Jtcy5kb3dubG9hZGZvcm0zRC50YXJnZXQgPSAncG9wdXBXaW4nOw0KCQkJaWYgKHN1Ym1pdHBvcHVwV2luID09IDEpIHsNCgkJCQlzZWxmLmRvY3VtZW50LmZvcm1zLmRvd25sb2FkZm9ybTNELnN1Ym1pdCgpOw0KCQkJfQ0KCQkJcG9wdXBXaW4uZm9jdXMoKTsNCgkJCXJldHVybiAwOw0KCQl9DQoJfSBlbHNlIHsNCgkJcmV0dXJuIDE7DQoJfQ0KfQ0KCXNlbGYuZG9jdW1lbnQuZm9ybXMuZG93bmxvYWRmb3JtM0Quc3VibWl0KCk7DQovLy0tPg0KPC9TQ1JJUFQ+DQo=", $arrParams['HTML_ANSWER']);
    }

    
    public function testWithNoXmlResponse()
    {
        $xmlExample = '';
        $this->assertTrue($this->setExpectedException('PHPUnit_Framework_ExpectationFailedException'));
        $arrParams = $this->_model->getParamArrFromXmlString($xmlExample);
        $xmlExample = 'N';
        $arrParams = $this->_model->getParamArrFromXmlString($xmlExample);
    }

    /**
     *  @expectedException Mage_Core_Exception
     */
    public function testIsInvalidResponseWithEmptyData()
    {
        $this->_model->checkResponse(null);
    }

    public function testIsValidResponse()
    {
        $this->assertTrue(is_null($this->_model->checkResponse(array('NCERROR' => 0))));
    }

    public function testGetResponseParamsWithValidCallResponse()
    {
        $xmlExample =
        '<?xml version="1.0"?>
            <ncresponse
                orderID="1121212"
                PAYID="232"
                PAYIDSUB="0"
                NCSTATUS="5"
                NCERROR="50001111"
                NCERRORPLUS="Some of the data entered is incorrect. Please retry."
                ACCEPTANCE=""
                STATUS="0"
                amount=""
                currency="">
                <HTML_ANSWER>PGZvcm0gbmFtZT0iZG93bmxvYWRmb3JtM0QiIGFjdGlvbj0iaHR0cHM6Ly9zZWN1cmUub2dvbmUuY29tL25jb2wvdGVzdC9UZXN0XzNEX0FDUy5hc3AiIG1ldGhvZD0icG9zdCI+DQo8Tk9TQ1JJUFQ+DQpKYXZhU2NyaXB0IGlzIGN1cnJlbnRseSBkaXNhYmxlZCBvciBpcyBub3Qgc3VwcG9ydGVkIGJ5IHlvdXIgYnJvd3Nlci48YnI+DQpQbGVhc2UgY2xpY2sgb24gdGhlICZxdW90O0NvbnRpbnVlJnF1b3Q7IGJ1dHRvbiB0byBjb250aW51ZSB0aGUgcHJvY2Vzc2luZyBvZiB5b3VyIDMtRCBzZWN1cmUgdHJhbnNhY3Rpb24uPGJyPg0KPGlucHV0IGNsYXNzPSJuY29sIiB0eXBlPSJzdWJtaXQiIHZhbHVlPSJDb250aW51ZSIgaWQ9InN1Ym1pdDEiIG5hbWU9InN1Ym1pdDEiIC8+DQo8L05PU0NSSVBUPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iQ1NSRktFWSIgdmFsdWU9IjA0MzA0MzI4NkE0M0ZDM0YyRDhFMDFCOUM2MzYwRTA1Qzg5NkZEMzYiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJDU1JGVFMiIHZhbHVlPSIyMDExMDkxMjE2MjQ1MCIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9IkNTUkZTUCIgdmFsdWU9Ii9uY29sL3Rlc3Qvb3JkZXJkaXJlY3QuYXNwIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iUGFSZXEiIHZhbHVlPSI8P3htbCB2ZXJzaW9uPSZxdW90OzEuMCZxdW90Oz8+PFRocmVlRFNlY3VyZT48TWVzc2FnZSBpZD0mcXVvdDsxMjMmcXVvdDs+PFBBUmVxPjx2ZXJzaW9uPjEuMDI8L3ZlcnNpb24+PE1lcmNoYW50PjxtZXJJRD5OUk1BR0VOVE8zPC9tZXJJRD48bmFtZT5OZXRyZXNlYXJjaCBHbWJIICZhbXA7YW1wOyBDby5LRzwvbmFtZT48dXJsPmh0dHA6Ly93d3cubmV0cmVzZWFyY2guZGU8L3VybD48L01lcmNoYW50PjxQdXJjaGFzZT48eGlkPjExNjI4OTgzPC94aWQ+PGFtb3VudD4xMjY4Ljc1PC9hbW91bnQ+PHB1cmNoQW1vdW50PjEyNjguNzU8L3B1cmNoQW1vdW50PjxjdXJyZW5jeT5FVVI8L2N1cnJlbmN5PjwvUHVyY2hhc2U+PENIPjxhY2N0SUQ+NDAwMDAwWFhYWFhYMDAwMjwvYWNjdElEPjxleHBpcnk+MDExNjwvZXhwaXJ5PjxzZWxCcmFuZD48L3NlbEJyYW5kPjwvQ0g+PC9QQVJlcT48L01lc3NhZ2U+PC9UaHJlZURTZWN1cmU+DQoiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJUZXJtVXJsIiB2YWx1ZT0iaHR0cHM6Ly9zZWN1cmUub2dvbmUuY29tL25jb2wvdGVzdC9vcmRlcl9BM0RTLmFzcCIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9Ik1EIiB2YWx1ZT0iTUFJTldQVEVTVDAwMDAxMTYyODk4MzAxKjEwNzUyNjIiIC8+DQo8L2Zvcm0+DQo8Zm9ybSBtZXRob2Q9InBvc3QiIGFjdGlvbj0iaHR0cHM6Ly9zZWN1cmUub2dvbmUuY29tL25jb2wvdGVzdC9vcmRlcl9hZ3JlZS5hc3AiIG5hbWU9InVwbG9hZEZvcm0zRCI+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJDU1JGS0VZIiB2YWx1ZT0iMDZGM0MzMUQ2RkI1MzIzODg4NjhFRjlGNTA5RUNGNzlBQzIwRDJGMyIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9IkNTUkZUUyIgdmFsdWU9IjIwMTEwOTEyMTYyNDUwIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iQ1NSRlNQIiB2YWx1ZT0iL25jb2wvdGVzdC9vcmRlcmRpcmVjdC5hc3AiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJicmFuZGluZyIgdmFsdWU9Ik9nb25lIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0icGF5aWQiIHZhbHVlPSIxMTYyODk4MyIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9InN0b3JlYWxpYXMiIHZhbHVlPSIiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJoYXNoX3BhcmFtIiB2YWx1ZT0iOTFBMzA1MjFEMEI0QTA1MEFBRDkzRDM5RDY2RkEyM0Y5OEIzRDQ4RCIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9InhpZF8zRCIgdmFsdWU9IiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9InN0YXR1c18zRCIgdmFsdWU9IlhYIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iZWNpXzNEIiB2YWx1ZT0iNyIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImNhcmRudW1iZXIiIHZhbHVlPSIiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJFY29tX1BheW1lbnRfQ2FyZF9WZXJpZmljYXRpb24iIHZhbHVlPSIqMTA3NTI2MiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9IkNWQ0ZsYWciIHZhbHVlPSIxIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iY2F2dl8zRCIgdmFsdWU9IiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImNhdnZhbGdvcml0aG1fM0QiIHZhbHVlPSIiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJzaWduYXR1cmVPS18zRCIgdmFsdWU9IiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9Imhhc2hfcGFyYW1fM0QiIHZhbHVlPSIwMzAzREZDMkI1OTM0MjZCQTExRkQ5RjJBNkQ0NDk5ODEwN0JGN0YzIiAvPg0KPC9mb3JtPg0KPFNDUklQVCBMQU5HVUFHRT0iSmF2YXNjcmlwdCIgRk9SPSJ3aW5kb3ciIEVWRU5UPSJvbkxvYWQiPg0KdmFyIHBvcHVwV2luOw0KdmFyIHN1Ym1pdHBvcHVwV2luID0gMDsNCg0KZnVuY3Rpb24gTG9hZFBvcHVwKCkgew0KCWlmIChzZWxmLm5hbWUgPT0gbnVsbCkJew0KCQlzZWxmLm5hbWUgPSAib2dvbmVNYWluIjsNCgl9DQoJcG9wdXBXaW4gPSB3aW5kb3cub3BlbignYWJvdXQ6YmxhbmsnLCAncG9wdXBXaW4nLCAnaGVpZ2h0PTQwMCwgd2lkdGg9MzkwLCBzdGF0dXM9eWVzLCBkZXBlbmRlbnQ9bm8sIHNjcm9sbGJhcnM9eWVzLCByZXNpemFibGU9bm8nKTsNCglpZiAocG9wdXBXaW4gIT0gbnVsbCkgew0KCQlpZiAgKCFwb3B1cFdpbiB8fCBwb3B1cFdpbi5jbG9zZWQpIHsNCgkJCXJldHVybiAxOw0KCQl9IGVsc2Ugew0KCQkJaWYgKCFwb3B1cFdpbi5vcGVuZXIgfHwgcG9wdXBXaW4ub3BlbmVyID09IG51bGwpIHsNCgkJCQlwb3B1cFdpbi5vcGVuZXIgPSBzZWxmOw0KCQkJfQ0KCQkJc2VsZi5kb2N1bWVudC5mb3Jtcy5kb3dubG9hZGZvcm0zRC50YXJnZXQgPSAncG9wdXBXaW4nOw0KCQkJaWYgKHN1Ym1pdHBvcHVwV2luID09IDEpIHsNCgkJCQlzZWxmLmRvY3VtZW50LmZvcm1zLmRvd25sb2FkZm9ybTNELnN1Ym1pdCgpOw0KCQkJfQ0KCQkJcG9wdXBXaW4uZm9jdXMoKTsNCgkJCXJldHVybiAwOw0KCQl9DQoJfSBlbHNlIHsNCgkJcmV0dXJuIDE7DQoJfQ0KfQ0KCXNlbGYuZG9jdW1lbnQuZm9ybXMuZG93bmxvYWRmb3JtM0Quc3VibWl0KCk7DQovLy0tPg0KPC9TQ1JJUFQ+DQo=</HTML_ANSWER>
            </ncresponse>';
        $directLinkMock = $this->getModelMock('ops/api_directlink', array('call'));
        $directLinkMock->expects($this->any())
            ->method('call')
            ->will($this->returnValue($xmlExample));
        
        $class = new ReflectionClass(get_class(Mage::getModel('ops/api_directlink')));
        $method = $class->getMethod('getResponseParams');
        $method->setAccessible(true);
        $result = $method->invokeArgs($directLinkMock, array(array('foo' => '111', 'ORDERID' => 4711), 'bar'));
        $this->assertTrue(is_array($result));
        $this->assertTrue(array_key_exists('orderID', $result));
        $this->assertTrue(array_key_exists('PAYID', $result));
        $this->assertTrue(array_key_exists('PAYIDSUB', $result));
        $this->assertTrue(array_key_exists('NCSTATUS', $result));
        $this->assertTrue(array_key_exists('NCERROR', $result));
        $this->assertTrue(array_key_exists('NCERROR', $result));
        $this->assertTrue(array_key_exists('NCERRORPLUS', $result));
        $this->assertTrue(array_key_exists('ACCEPTANCE', $result));
        $this->assertTrue(array_key_exists('STATUS', $result));
        $this->assertTrue(array_key_exists('amount', $result));
        $this->assertTrue(array_key_exists('currency', $result));
        $this->assertTrue(array_key_exists('HTML_ANSWER', $result));
    }

    public function testGetResponseParamsWithValidCallButWrongEncodedResponse()
    {
        $xmlExample =
        '<?xml version="1.0"?>
            <ncresponse
                orderID="1121212"
                PAYID="232"
                PAYIDSUB="0"
                NCSTATUS="5"
                NCERROR="50001111"
                NCERRORPLUS="Some of the data entered is incorrect. Please retry."
                ACCEPTANCE=""
                STATUS="0"
                amount=""
                currency=""
                CN="Max Mueller">
                <HTML_ANSWER>PGZvcm0gbmFtZT0iZG93bmxvYWRmb3JtM0QiIGFjdGlvbj0iaHR0cHM6Ly9zZWN1cmUub2dvbmUuY29tL25jb2wvdGVzdC9UZXN0XzNEX0FDUy5hc3AiIG1ldGhvZD0icG9zdCI+DQo8Tk9TQ1JJUFQ+DQpKYXZhU2NyaXB0IGlzIGN1cnJlbnRseSBkaXNhYmxlZCBvciBpcyBub3Qgc3VwcG9ydGVkIGJ5IHlvdXIgYnJvd3Nlci48YnI+DQpQbGVhc2UgY2xpY2sgb24gdGhlICZxdW90O0NvbnRpbnVlJnF1b3Q7IGJ1dHRvbiB0byBjb250aW51ZSB0aGUgcHJvY2Vzc2luZyBvZiB5b3VyIDMtRCBzZWN1cmUgdHJhbnNhY3Rpb24uPGJyPg0KPGlucHV0IGNsYXNzPSJuY29sIiB0eXBlPSJzdWJtaXQiIHZhbHVlPSJDb250aW51ZSIgaWQ9InN1Ym1pdDEiIG5hbWU9InN1Ym1pdDEiIC8+DQo8L05PU0NSSVBUPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iQ1NSRktFWSIgdmFsdWU9IjA0MzA0MzI4NkE0M0ZDM0YyRDhFMDFCOUM2MzYwRTA1Qzg5NkZEMzYiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJDU1JGVFMiIHZhbHVlPSIyMDExMDkxMjE2MjQ1MCIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9IkNTUkZTUCIgdmFsdWU9Ii9uY29sL3Rlc3Qvb3JkZXJkaXJlY3QuYXNwIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iUGFSZXEiIHZhbHVlPSI8P3htbCB2ZXJzaW9uPSZxdW90OzEuMCZxdW90Oz8+PFRocmVlRFNlY3VyZT48TWVzc2FnZSBpZD0mcXVvdDsxMjMmcXVvdDs+PFBBUmVxPjx2ZXJzaW9uPjEuMDI8L3ZlcnNpb24+PE1lcmNoYW50PjxtZXJJRD5OUk1BR0VOVE8zPC9tZXJJRD48bmFtZT5OZXRyZXNlYXJjaCBHbWJIICZhbXA7YW1wOyBDby5LRzwvbmFtZT48dXJsPmh0dHA6Ly93d3cubmV0cmVzZWFyY2guZGU8L3VybD48L01lcmNoYW50PjxQdXJjaGFzZT48eGlkPjExNjI4OTgzPC94aWQ+PGFtb3VudD4xMjY4Ljc1PC9hbW91bnQ+PHB1cmNoQW1vdW50PjEyNjguNzU8L3B1cmNoQW1vdW50PjxjdXJyZW5jeT5FVVI8L2N1cnJlbmN5PjwvUHVyY2hhc2U+PENIPjxhY2N0SUQ+NDAwMDAwWFhYWFhYMDAwMjwvYWNjdElEPjxleHBpcnk+MDExNjwvZXhwaXJ5PjxzZWxCcmFuZD48L3NlbEJyYW5kPjwvQ0g+PC9QQVJlcT48L01lc3NhZ2U+PC9UaHJlZURTZWN1cmU+DQoiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJUZXJtVXJsIiB2YWx1ZT0iaHR0cHM6Ly9zZWN1cmUub2dvbmUuY29tL25jb2wvdGVzdC9vcmRlcl9BM0RTLmFzcCIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9Ik1EIiB2YWx1ZT0iTUFJTldQVEVTVDAwMDAxMTYyODk4MzAxKjEwNzUyNjIiIC8+DQo8L2Zvcm0+DQo8Zm9ybSBtZXRob2Q9InBvc3QiIGFjdGlvbj0iaHR0cHM6Ly9zZWN1cmUub2dvbmUuY29tL25jb2wvdGVzdC9vcmRlcl9hZ3JlZS5hc3AiIG5hbWU9InVwbG9hZEZvcm0zRCI+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJDU1JGS0VZIiB2YWx1ZT0iMDZGM0MzMUQ2RkI1MzIzODg4NjhFRjlGNTA5RUNGNzlBQzIwRDJGMyIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9IkNTUkZUUyIgdmFsdWU9IjIwMTEwOTEyMTYyNDUwIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iQ1NSRlNQIiB2YWx1ZT0iL25jb2wvdGVzdC9vcmRlcmRpcmVjdC5hc3AiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJicmFuZGluZyIgdmFsdWU9Ik9nb25lIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0icGF5aWQiIHZhbHVlPSIxMTYyODk4MyIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9InN0b3JlYWxpYXMiIHZhbHVlPSIiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJoYXNoX3BhcmFtIiB2YWx1ZT0iOTFBMzA1MjFEMEI0QTA1MEFBRDkzRDM5RDY2RkEyM0Y5OEIzRDQ4RCIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9InhpZF8zRCIgdmFsdWU9IiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9InN0YXR1c18zRCIgdmFsdWU9IlhYIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iZWNpXzNEIiB2YWx1ZT0iNyIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImNhcmRudW1iZXIiIHZhbHVlPSIiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJFY29tX1BheW1lbnRfQ2FyZF9WZXJpZmljYXRpb24iIHZhbHVlPSIqMTA3NTI2MiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9IkNWQ0ZsYWciIHZhbHVlPSIxIiAvPg0KPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iY2F2dl8zRCIgdmFsdWU9IiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImNhdnZhbGdvcml0aG1fM0QiIHZhbHVlPSIiIC8+DQo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJzaWduYXR1cmVPS18zRCIgdmFsdWU9IiIgLz4NCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9Imhhc2hfcGFyYW1fM0QiIHZhbHVlPSIwMzAzREZDMkI1OTM0MjZCQTExRkQ5RjJBNkQ0NDk5ODEwN0JGN0YzIiAvPg0KPC9mb3JtPg0KPFNDUklQVCBMQU5HVUFHRT0iSmF2YXNjcmlwdCIgRk9SPSJ3aW5kb3ciIEVWRU5UPSJvbkxvYWQiPg0KdmFyIHBvcHVwV2luOw0KdmFyIHN1Ym1pdHBvcHVwV2luID0gMDsNCg0KZnVuY3Rpb24gTG9hZFBvcHVwKCkgew0KCWlmIChzZWxmLm5hbWUgPT0gbnVsbCkJew0KCQlzZWxmLm5hbWUgPSAib2dvbmVNYWluIjsNCgl9DQoJcG9wdXBXaW4gPSB3aW5kb3cub3BlbignYWJvdXQ6YmxhbmsnLCAncG9wdXBXaW4nLCAnaGVpZ2h0PTQwMCwgd2lkdGg9MzkwLCBzdGF0dXM9eWVzLCBkZXBlbmRlbnQ9bm8sIHNjcm9sbGJhcnM9eWVzLCByZXNpemFibGU9bm8nKTsNCglpZiAocG9wdXBXaW4gIT0gbnVsbCkgew0KCQlpZiAgKCFwb3B1cFdpbiB8fCBwb3B1cFdpbi5jbG9zZWQpIHsNCgkJCXJldHVybiAxOw0KCQl9IGVsc2Ugew0KCQkJaWYgKCFwb3B1cFdpbi5vcGVuZXIgfHwgcG9wdXBXaW4ub3BlbmVyID09IG51bGwpIHsNCgkJCQlwb3B1cFdpbi5vcGVuZXIgPSBzZWxmOw0KCQkJfQ0KCQkJc2VsZi5kb2N1bWVudC5mb3Jtcy5kb3dubG9hZGZvcm0zRC50YXJnZXQgPSAncG9wdXBXaW4nOw0KCQkJaWYgKHN1Ym1pdHBvcHVwV2luID09IDEpIHsNCgkJCQlzZWxmLmRvY3VtZW50LmZvcm1zLmRvd25sb2FkZm9ybTNELnN1Ym1pdCgpOw0KCQkJfQ0KCQkJcG9wdXBXaW4uZm9jdXMoKTsNCgkJCXJldHVybiAwOw0KCQl9DQoJfSBlbHNlIHsNCgkJcmV0dXJuIDE7DQoJfQ0KfQ0KCXNlbGYuZG9jdW1lbnQuZm9ybXMuZG93bmxvYWRmb3JtM0Quc3VibWl0KCk7DQovLy0tPg0KPC9TQ1JJUFQ+DQo=</HTML_ANSWER>
            </ncresponse>';
        $xmlExample = utf8_decode($xmlExample);
        $directLinkMock = $this->getModelMock('ops/api_directlink', array('call'));
        $directLinkMock->expects($this->any())
            ->method('call')
            ->will($this->returnValue($xmlExample));

        $class = new ReflectionClass(get_class(Mage::getModel('ops/api_directlink')));
        $method = $class->getMethod('getResponseParams');
        $method->setAccessible(true);
        $result = $method->invokeArgs($directLinkMock, array(array('foo' => '111', 'ORDERID' => 4711), 'bar'));
        $this->assertTrue(is_array($result));
        $this->assertTrue(array_key_exists('orderID', $result));
        $this->assertTrue(array_key_exists('PAYID', $result));
        $this->assertTrue(array_key_exists('PAYIDSUB', $result));
        $this->assertTrue(array_key_exists('NCSTATUS', $result));
        $this->assertTrue(array_key_exists('NCERROR', $result));
        $this->assertTrue(array_key_exists('NCERROR', $result));
        $this->assertTrue(array_key_exists('NCERRORPLUS', $result));
        $this->assertTrue(array_key_exists('ACCEPTANCE', $result));
        $this->assertTrue(array_key_exists('STATUS', $result));
        $this->assertTrue(array_key_exists('amount', $result));
        $this->assertTrue(array_key_exists('currency', $result));
        $this->assertTrue(array_key_exists('HTML_ANSWER', $result));
        $this->assertTrue(array_key_exists('CN', $result));
        $this->assertEquals('Max Mueller', $result['CN']);
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testGetResponseParamsWithRetryCountExceeded()
    {
        $maxAllowedRetryCount = Netresearch_OPS_Model_Api_DirectLink::MAX_RETRY_COUNT;
        $class = new ReflectionClass(get_class(Mage::getModel('ops/api_directlink')));
        $method = $class->getMethod('getResponseParams');
        $method->setAccessible(true);
        $result = $method->invokeArgs(Mage::getModel('ops/api_directlink'), array(array('foo' => '123', 'ORDERID' => '4711'), 'bar', ++$maxAllowedRetryCount));
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testGetResponseParamsWithInvalidResponse()
    {
        $xmlExample = '';
        $directLinkMock = $this->getModelMock('ops/api_directlink', array('call'));
        $directLinkMock->expects($this->any())
            ->method('call')
            ->will($this->returnValue($xmlExample));
        
        $class = new ReflectionClass(get_class(Mage::getModel('ops/api_directlink')));
        $method = $class->getMethod('getResponseParams');
        $method->setAccessible(true);
        $result = $method->invokeArgs($directLinkMock, array(array('foo' => '12334', 'ORDERID' => 4711), 'bar'));
    }
    
    /**
     * @expectedException Mage_Core_Exception
     */
    public function testCheckResponseNcErrorSet()
    {
        $responseParams = array('NCERROR' => '50001111');
        $this->_model->checkResponse($responseParams);
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testCheckResponseNcErrorEmpty()
    {
        $responseParams = array('NCERROR' => '1', 'NCERRORPLUS' => 'Error occured');
        $this->_model->checkResponse($responseParams);
    }
    
    public function testCheckResponseNrErrorWithStatus()
    {
        $responseParams = array('NCERROR' => '1', 'STATUS' => 4);
        try {
            $this->_model->checkResponse($responseParams);
        }
        catch (Exception $e) {
            $this->fail('An unexpected exception has been raised.');
        }
    }
    
    public function testPerformRequest()
    {
        $result = array('NCERROR' => 0);
        $directLinkMock = $this->getModelMock('ops/api_directlink', array('getResponseParams'));
        $directLinkMock->expects($this->any())
            ->method('getResponseParams')
            ->will($this->returnValue($result));
        $this->assertEquals($result, $directLinkMock->performRequest(array('foo'), 'bla'));
        
    }
}

