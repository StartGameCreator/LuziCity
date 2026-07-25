<?php

namespace Tests\Unit\Security;

use App\Services\Security\EmbedCodeSanitizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EmbedCodeSanitizerTest extends TestCase
{
    #[Test]
    public function it_keeps_a_safe_youtube_iframe_and_removes_event_handlers(): void
    {
        $result = EmbedCodeSanitizer::sanitize(
            '<iframe src="https://www.youtube.com/embed/abc123" onload="alert(1)" allowfullscreen></iframe>'
        );

        $this->assertStringContainsString('https://www.youtube.com/embed/abc123', $result);
        $this->assertStringNotContainsString('onload', $result);
        $this->assertStringContainsString('loading="lazy"', $result);
    }

    #[Test]
    public function it_rejects_scripts_and_javascript_urls(): void
    {
        $result = EmbedCodeSanitizer::sanitize(
            '<script>alert(1)</script><iframe src="javascript:alert(1)"></iframe>'
        );

        $this->assertSame('', $result);
    }

    #[Test]
    public function it_rejects_iframes_from_unapproved_hosts(): void
    {
        $result = EmbedCodeSanitizer::sanitize(
            '<iframe src="https://evil.example/video"></iframe>'
        );

        $this->assertSame('', $result);
    }

    #[Test]
    public function it_accepts_supported_facebook_and_dlive_sources(): void
    {
        $facebook = EmbedCodeSanitizer::sanitize('<iframe src="https://www.facebook.com/plugins/video.php?id=1"></iframe>');
        $dlive = EmbedCodeSanitizer::sanitize('<iframe src="https://player.dlive.tv/video/test"></iframe>');

        $this->assertNotSame('', $facebook);
        $this->assertNotSame('', $dlive);
    }
}
