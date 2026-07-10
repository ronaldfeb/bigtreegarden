<?php

use App\Models\Blog;
use App\Models\HelpCenterArticle;
use App\Models\HelpCenterTopic;
use App\Models\MarketingAdvert;
use App\Models\MarketingAdvertVisit;
use App\Models\Partner;
use App\Models\Policy;
use App\Models\ServiceProvider;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionPackageFeature;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('renders the pricing page with active packages', function () {
    $package = SubscriptionPackage::factory()->featured()->create(['name' => 'Family Plan']);
    SubscriptionPackageFeature::factory()->create([
        'subscription_package_id' => $package->id,
        'label' => 'Unlimited gallery images',
    ]);
    SubscriptionPackage::factory()->inactive()->create(['name' => 'Hidden Plan']);

    $this->get(route('pricing'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/Pricing')
            ->has('packages', 1)
            ->where('packages.0.name', 'Family Plan')
            ->has('packages.0.features', 1),
        );
});

it('renders published blogs and hides drafts', function () {
    $published = Blog::factory()->published()->create(['title' => 'Published Post']);
    Blog::factory()->create(['title' => 'Draft Post']);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/Blog/Index')
            ->has('blogs', 1)
            ->where('blogs.0.title', 'Published Post'),
        );
});

it('renders a published blog post', function () {
    $blog = Blog::factory()->published()->create(['title' => 'Memorial Guide']);

    $this->get(route('blog.show', $blog))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/Blog/Show')
            ->where('blog.title', 'Memorial Guide'),
        );
});

it('returns not found for draft blog posts', function () {
    $blog = Blog::factory()->create();

    $this->get(route('blog.show', $blog))->assertNotFound();
});

it('renders the help center index with active topics', function () {
    $topic = HelpCenterTopic::factory()->create(['name' => 'Getting Started']);
    HelpCenterArticle::factory()->published()->create(['help_center_topic_id' => $topic->id]);
    HelpCenterTopic::factory()->inactive()->create(['name' => 'Hidden Topic']);

    $this->get(route('help.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/Help/Index')
            ->has('topics', 1)
            ->where('topics.0.name', 'Getting Started')
            ->where('topics.0.published_articles_count', 1),
        );
});

it('renders a help center topic with published articles', function () {
    $topic = HelpCenterTopic::factory()->create(['name' => 'Account']);
    $article = HelpCenterArticle::factory()->published()->create([
        'help_center_topic_id' => $topic->id,
        'title' => 'Reset your password',
    ]);
    HelpCenterArticle::factory()->create([
        'help_center_topic_id' => $topic->id,
        'title' => 'Draft article',
    ]);

    $this->get(route('help.topic', $topic))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/Help/Topic')
            ->where('topic.name', 'Account')
            ->has('articles', 1)
            ->where('articles.0.title', 'Reset your password'),
        );
});

it('renders a help center article within its topic', function () {
    $topic = HelpCenterTopic::factory()->create();
    $article = HelpCenterArticle::factory()->published()->create([
        'help_center_topic_id' => $topic->id,
        'title' => 'How to share a memorial page',
    ]);

    $this->get(route('help.article', [$topic, $article]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/Help/Article')
            ->where('article.title', 'How to share a memorial page'),
        );
});

it('returns not found when help article does not belong to topic', function () {
    $topic = HelpCenterTopic::factory()->create();
    $otherTopic = HelpCenterTopic::factory()->create();
    $article = HelpCenterArticle::factory()->published()->create([
        'help_center_topic_id' => $otherTopic->id,
    ]);

    $this->get(route('help.article', [$topic, $article]))->assertNotFound();
});

it('renders published policy pages', function () {
    Policy::factory()->create([
        'type' => config('constants.policy.type.terms_of_service'),
        'title' => 'Terms of Service',
    ]);
    Policy::factory()->create([
        'type' => config('constants.policy.type.privacy_policy'),
        'title' => 'Privacy Policy',
    ]);
    Policy::factory()->create([
        'type' => config('constants.policy.type.about_us'),
        'title' => 'About Us',
    ]);

    $this->get(route('policies.terms'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/Policy/Show')
            ->where('policy.title', 'Terms of Service'),
        );

    $this->get(route('policies.privacy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/Policy/Show')
            ->where('policy.title', 'Privacy Policy'),
        );

    $this->get(route('policies.about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/Policy/Show')
            ->where('policy.title', 'About Us'),
        );
});

it('returns 404 for unpublished policy pages', function () {
    Policy::factory()->create([
        'type' => config('constants.policy.type.terms_of_service'),
        'published_at' => null,
    ]);

    $this->get(route('policies.terms'))->assertNotFound();
});

it('renders active service providers', function () {
    ServiceProvider::factory()->active()->create(['name' => 'Active Provider']);
    ServiceProvider::factory()->create(['name' => 'Pending Provider']);

    $this->get(route('providers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/Providers/Index')
            ->has('providers', 1)
            ->where('providers.0.name', 'Active Provider'),
        );
});

it('renders an active service provider profile', function () {
    $provider = ServiceProvider::factory()->active()->create(['name' => 'Heritage Funerals']);

    $this->get(route('providers.show', $provider))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/Providers/Show')
            ->where('provider.name', 'Heritage Funerals'),
        );
});

it('logs a marketing advert visit and redirects with utm params', function () {
    $advert = MarketingAdvert::factory()->create([
        'code' => 'qr01',
        'destination_url' => 'https://example.com/welcome',
        'utm_source' => 'poster',
        'utm_medium' => 'qr',
        'utm_campaign' => 'spring',
        'utm_content' => 'lobby',
    ]);

    $this->get(route('marketing.adverts.redirect', 'qr01'))
        ->assertRedirect('https://example.com/welcome?utm_source=poster&utm_medium=qr&utm_campaign=spring&utm_content=lobby');

    expect(MarketingAdvertVisit::query()->count())->toBe(1);

    $visit = MarketingAdvertVisit::query()->first();
    expect($visit->marketing_advert_id)->toBe($advert->id);
    expect($visit->visitor_hash)->toBe(hash('sha256', '127.0.0.1Symfony'));
});

it('includes featured published testimonials on the landing page', function () {
    Testimonial::factory()->featured()->create(['name' => 'Featured Voice']);
    Testimonial::factory()->featured()->draft()->create(['name' => 'Draft Voice']);
    Testimonial::factory()->create(['name' => 'Regular Voice', 'is_featured' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('testimonials', 1)
            ->where('testimonials.0.name', 'Featured Voice'),
        );
});

it('renders active partners on the partners page', function () {
    Partner::factory()->create(['name' => 'Visible Partner', 'sort_order' => 0]);
    Partner::factory()->inactive()->create(['name' => 'Hidden Partner']);

    $this->get(route('partners.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/Partners/Index')
            ->has('partners', 1)
            ->where('partners.0.name', 'Visible Partner'),
        );
});

it('includes active partners on the landing page', function () {
    Partner::factory()->create(['name' => 'Active Partner']);
    Partner::factory()->inactive()->create(['name' => 'Inactive Partner']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('partners', 1)
            ->where('partners.0.name', 'Active Partner'),
        );
});
