<?php

it('returns an empty metadata value when the requested seo page is missing', function () {
    expect(metaData('missing-seo-page'))->toBe('');
});
