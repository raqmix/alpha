<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Temporary upgrade smoke test: walks every registered Filament resource and
 * page in the admin panel and asserts it renders. This exercises each
 * resource's form()/table() schema, which route registration alone does not.
 */
class PanelSmokeTest extends TestCase
{
    public function test_every_admin_resource_and_page_renders(): void
    {
        $user = User::find(1);
        $this->assertNotNull($user, 'No user with id=1 to authenticate as.');

        Filament::setCurrentPanel('admin');
        $this->actingAs($user);

        $failures = [];
        $ok = 0;

        $panel = Filament::getPanel('admin');

        foreach ($panel->getResources() as $resource) {
            foreach (['index', 'create'] as $page) {
                if (! array_key_exists($page, $resource::getPages())) {
                    continue;
                }

                try {
                    $url = $resource::getUrl($page);
                } catch (\Throwable $e) {
                    continue; // nested/parameterised resources
                }

                try {
                    $response = $this->get($url);
                    $status = $response->getStatusCode();

                    if ($status >= 500) {
                        $e = $response->baseResponse->exception ?? null;
                        $failures[] = sprintf(
                            '%s [%s] -> %d %s',
                            class_basename($resource),
                            $page,
                            $status,
                            $e ? class_basename($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() : ''
                        );
                    } else {
                        $ok++;
                    }
                } catch (\Throwable $e) {
                    $failures[] = sprintf('%s [%s] -> %s: %s', class_basename($resource), $page, class_basename($e), $e->getMessage());
                }
            }
        }

        foreach ($panel->getPages() as $pageClass) {
            try {
                $url = $pageClass::getUrl();
            } catch (\Throwable $e) {
                continue;
            }

            try {
                $response = $this->get($url);

                if ($response->getStatusCode() >= 500) {
                    $e = $response->baseResponse->exception ?? null;
                    $failures[] = sprintf(
                        '%s -> %d %s',
                        class_basename($pageClass),
                        $response->getStatusCode(),
                        $e ? class_basename($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() : ''
                    );
                } else {
                    $ok++;
                }
            } catch (\Throwable $e) {
                $failures[] = sprintf('%s -> %s: %s', class_basename($pageClass), class_basename($e), $e->getMessage());
            }
        }

        fwrite(STDERR, "\n  rendered OK: {$ok}\n");

        if ($failures) {
            fwrite(STDERR, "  FAILURES (" . count($failures) . "):\n");
            foreach ($failures as $f) {
                fwrite(STDERR, "    - {$f}\n");
            }
        }

        $this->assertSame([], $failures, 'Some panel pages failed to render.');
    }
}
