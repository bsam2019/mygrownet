<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuideController extends Controller
{
    public function index()
    {
        return Inertia::render('BizBoost/Guides/Index', [
            'guides' => $this->getGuideList(),
        ]);
    }

    public function show(string $slug)
    {
        $guide = $this->getGuide($slug);
        if (!$guide) {
            abort(404);
        }
        return Inertia::render('BizBoost/Guides/Show', [
            'guide' => $guide,
        ]);
    }

    private function getGuideList(): array
    {
        return [
            ['slug' => 'getting-started', 'title' => 'Getting Started with BizBoost', 'description' => 'Overview of BizBoost features and how to navigate the module.', 'icon' => 'RocketLaunchIcon', 'color' => 'blue'],
            ['slug' => 'wallet', 'title' => 'Wallet & Payments', 'description' => 'Deposit funds, check balances, and understand the escrow system.', 'icon' => 'WalletIcon', 'color' => 'green'],
            ['slug' => 'ad-campaigns', 'title' => 'Ad Campaigns (Meta/Facebook)', 'description' => 'Create, launch, and manage Facebook ad campaigns with wallet funding.', 'icon' => 'CurrencyDollarIcon', 'color' => 'orange'],
            ['slug' => 'omnichannel', 'title' => 'Omnichannel Messaging', 'description' => 'Send WhatsApp and SMS messages to customers.', 'icon' => 'ChatBubbleBottomCenterTextIcon', 'color' => 'emerald'],
            ['slug' => 'social-integrations', 'title' => 'Facebook & Instagram Integration', 'description' => 'Connect your social media accounts and publish posts.', 'icon' => 'LinkIcon', 'color' => 'indigo'],
            ['slug' => 'posts', 'title' => 'Posts & Scheduling', 'description' => 'Create, publish, and schedule social media posts.', 'icon' => 'DocumentTextIcon', 'color' => 'purple'],
            ['slug' => 'admin-billing', 'title' => 'Admin Billing Ledger', 'description' => 'View revenue, vendor costs, and platform profit.', 'icon' => 'ChartBarIcon', 'color' => 'red'],
        ];
    }

    private function getGuide(string $slug): ?array
    {
        $guides = [
            'getting-started' => [
                'slug' => 'getting-started',
                'title' => 'Getting Started with BizBoost',
                'icon' => 'RocketLaunchIcon',
                'color' => 'blue',
                'sections' => [
                    [
                        'heading' => 'What is BizBoost?',
                        'content' => 'BizBoost is your all-in-one business management and marketing platform. It helps you manage products, customers, sales, social media posts, Facebook ad campaigns, WhatsApp messaging, and more — all from one dashboard.',
                    ],
                    [
                        'heading' => 'Accessing BizBoost',
                        'steps' => [
                            'Log in to your MyGrowNet account.',
                            'From the dashboard, click the "BizBoost" card in the Business Tools section.',
                            'You will land on the BizBoost Dashboard with the sidebar navigation on the left.',
                        ],
                    ],
                    [
                        'heading' => 'Navigation Overview',
                        'content' => 'The sidebar is organized into collapsible sections:',
                        'bullets' => [
                            ['label' => 'Business Operations', 'desc' => 'Products, Customers, Sales, Posts, Calendar, Analytics'],
                            ['label' => 'Tools', 'desc' => 'AI Content, Templates, Campaigns, Ad Campaigns, Wallet, Omnichannel, WhatsApp, AI Advisor, Reminders'],
                            ['label' => 'Advanced', 'desc' => 'Business Profile, Mini-Website, Integrations, Industry Kits, Learning Hub, Team, Messages, Locations, API Access, Marketplace, White Label'],
                        ],
                    ],
                    [
                        'heading' => 'Before You Start',
                        'content' => 'Some features require funds in your BizBoost Wallet. Go to Tools → Wallet to deposit before creating ad campaigns.',
                        'note' => 'Ad campaigns require a sufficient available balance. The wallet uses an escrow system: funds are locked when you create a campaign and withdrawn only when you launch it.',
                    ],
                ],
            ],

            'wallet' => [
                'slug' => 'wallet',
                'title' => 'Wallet & Payments',
                'icon' => 'WalletIcon',
                'color' => 'green',
                'sections' => [
                    [
                        'heading' => 'Overview',
                        'content' => 'The BizBoost Wallet is your pre-funded account used to pay for ad campaigns and messaging services. All transactions are recorded and visible in the wallet dashboard.',
                    ],
                    [
                        'heading' => 'Depositing Funds',
                        'steps' => [
                            'Navigate to Tools → Wallet.',
                            'Enter the amount you wish to deposit (e.g. 50000).',
                            'Click "Deposit". The amount is credited to your wallet immediately.',
                        ],
                        'note' => 'Deposits are currently in test mode — no real payment is processed. In production, you will be redirected to a payment gateway (MoneyUnify/PawaPay).',
                    ],
                    [
                        'heading' => 'Understanding Your Balance',
                        'bullets' => [
                            ['label' => 'Total Balance', 'desc' => 'The full amount in your wallet.'],
                            ['label' => 'Locked Balance', 'desc' => 'Funds reserved for pending ad campaigns. Not available for spending.'],
                            ['label' => 'Available Balance', 'desc' => 'Total Balance minus Locked Balance. This is what you can spend.'],
                        ],
                    ],
                    [
                        'heading' => 'The Escrow System',
                        'content' => 'When you create an ad campaign, the budget is locked (escrowed) from your wallet. The money is not spent yet — it is just reserved. When you launch the campaign, the locked funds are withdrawn. If you delete a draft campaign, the locked funds are released back to your available balance.',
                    ],
                    [
                        'heading' => 'Transaction History',
                        'content' => 'The wallet page shows a paginated list of all deposits and withdrawals with date, amount, type, and description.',
                    ],
                ],
            ],

            'ad-campaigns' => [
                'slug' => 'ad-campaigns',
                'title' => 'Ad Campaigns (Meta/Facebook)',
                'icon' => 'CurrencyDollarIcon',
                'color' => 'orange',
                'sections' => [
                    [
                        'heading' => 'Overview',
                        'content' => 'BizBoost lets you create and manage Facebook ad campaigns directly from your dashboard. Campaigns are funded through your BizBoost Wallet. BizBoost adds a 20% markup on ad spend.',
                    ],
                    [
                        'heading' => 'How pricing works',
                        'content' => 'When you set a budget of $100, you are charged $120. Meta spends $100 on your ads, and BizBoost keeps $20 as platform profit. The markup is displayed live on the create form.',
                    ],
                    [
                        'heading' => 'Creating a Campaign',
                        'steps' => [
                            'Go to Tools → Ad Campaigns → Create Campaign.',
                            'Enter a campaign name (e.g. "Summer Sale 2026").',
                            'Select an objective: Traffic, Engagement, Leads, Sales, or Brand Awareness.',
                            'Set your budget. The form shows the 20% markup in real time.',
                            'Optionally set start and end dates (defaults to today and +30 days).',
                            'Click "Create Campaign". The budget is locked from your wallet.',
                        ],
                    ],
                    [
                        'heading' => 'Launching a Campaign',
                        'steps' => [
                            'After creation, you are redirected to the campaign detail page.',
                            'Review the campaign details and budget.',
                            'Click "Launch Campaign". This sends the campaign to Meta and withdraws funds from your wallet.',
                        ],
                        'note' => 'Once launched, the campaign goes to Meta for review. Active campaigns can be paused and resumed.',
                    ],
                    [
                        'heading' => 'Pausing and Resuming',
                        'steps' => [
                            'On the campaign detail page, click "Pause" to stop ad delivery.',
                            'Click "Resume" to restart delivery.',
                            'Paused campaigns do not accrue additional costs.',
                        ],
                    ],
                    [
                        'heading' => 'Viewing Insights',
                        'content' => 'The campaign detail page shows real-time metrics from Meta: impressions, clicks, amount spent, CTR (click-through rate), and CPC (cost per click). These update each time you visit the page.',
                    ],
                    [
                        'heading' => 'Auto-Pause on Zero Balance',
                        'content' => 'If your wallet runs out of funds, the system automatically pauses all active campaigns within 15 minutes. Top up your wallet to resume.',
                    ],
                ],
            ],

            'omnichannel' => [
                'slug' => 'omnichannel',
                'title' => 'Omnichannel Messaging',
                'icon' => 'ChatBubbleBottomCenterTextIcon',
                'color' => 'emerald',
                'sections' => [
                    [
                        'heading' => 'Overview',
                        'content' => 'The Omnichannel tool lets you send messages to customers via WhatsApp or SMS from a single interface. BizBoost tries WhatsApp first, then falls back to SMS if WhatsApp is unavailable.',
                    ],
                    [
                        'heading' => 'Sending a Message',
                        'steps' => [
                            'Go to Tools → Omnichannel.',
                            'Enter the recipient phone number in international format (e.g. 260965896512).',
                            'Select the channel: WhatsApp (preferred) or SMS.',
                            'Type your message.',
                            'Click "Send".',
                        ],
                    ],
                    [
                        'heading' => 'Delivery Log',
                        'content' => 'The page displays a delivery log showing all sent messages with: recipient, channel used, delivery status (delivered/failed), amount charged, and platform profit.',
                    ],
                    [
                        'heading' => 'Pricing',
                        'content' => 'Each message costs a small fee. The client charge and vendor cost are shown in the delivery log. The difference is platform profit.',
                    ],
                    [
                        'heading' => 'WhatsApp Requirements',
                        'bullets' => [
                            ['label' => 'Opt-in rule', 'desc' => 'Customers must initiate the first message to your business (via wa.me link). You can then reply within a 24-hour window.'],
                            ['label' => 'Business number', 'desc' => 'Your WhatsApp Business number must be set up in Meta Business Platform.'],
                        ],
                    ],
                ],
            ],

            'social-integrations' => [
                'slug' => 'social-integrations',
                'title' => 'Facebook & Instagram Integration',
                'icon' => 'LinkIcon',
                'color' => 'indigo',
                'sections' => [
                    [
                        'heading' => 'Overview',
                        'content' => 'Connect your Facebook Page and Instagram Business Account to BizBoost to publish posts directly from the dashboard without logging into each platform separately.',
                    ],
                    [
                        'heading' => 'Connecting Facebook',
                        'steps' => [
                            'Go to Advanced → Integrations.',
                            'Click "Connect Facebook".',
                            'You will be redirected to Facebook\'s login dialog.',
                            'Log in to Facebook and grant the requested permissions: manage pages, create posts, read insights.',
                            'After approval, you are redirected back to BizBoost. Your connected pages appear in the integrations list.',
                        ],
                    ],
                    [
                        'heading' => 'Connecting Instagram',
                        'content' => 'Instagram uses the same Facebook connection. Once Facebook is connected and your Instagram Business Account is linked to your Facebook Page, Instagram integration is available automatically.',
                    ],
                    [
                        'heading' => 'Managing Integrations',
                        'bullets' => [
                            ['label' => 'View status', 'desc' => 'See which accounts are connected and active.'],
                            ['label' => 'Disconnect', 'desc' => 'Remove a connected account at any time.'],
                            ['label' => 'Refresh token', 'desc' => 'If your session expires, use the Refresh option to renew without reconnecting.'],
                        ],
                    ],
                ],
            ],

            'posts' => [
                'slug' => 'posts',
                'title' => 'Posts & Scheduling',
                'icon' => 'DocumentTextIcon',
                'color' => 'purple',
                'sections' => [
                    [
                        'heading' => 'Overview',
                        'content' => 'Create, publish, and schedule social media posts for your connected Facebook and Instagram accounts.',
                    ],
                    [
                        'heading' => 'Creating a Post',
                        'steps' => [
                            'Go to Business Operations → Posts → Create Post.',
                            'Enter your post content (text/caption).',
                            'Optionally upload images or videos.',
                            'Select the social accounts to publish to (requires connected integrations).',
                            'Choose to publish now or schedule for later.',
                        ],
                    ],
                    [
                        'heading' => 'Publishing',
                        'steps' => [
                            'Click "Publish Now" to post immediately.',
                            'The system sends the post to each selected social platform via their APIs.',
                            'After publishing, the post status changes to "Published" with a link to the live post.',
                        ],
                    ],
                    [
                        'heading' => 'Scheduling Posts',
                        'steps' => [
                            'When creating a post, select a future date and time.',
                            'The post status is set to "Scheduled".',
                            'The system automatically publishes it at the scheduled time.',
                            'You can view all scheduled posts in the calendar view.',
                        ],
                    ],
                    [
                        'heading' => 'Managing Posts',
                        'bullets' => [
                            ['label' => 'Edit', 'desc' => 'Modify draft or scheduled posts before publishing.'],
                            ['label' => 'Delete', 'desc' => 'Remove posts that are not yet published.'],
                            ['label' => 'Duplicate', 'desc' => 'Copy an existing post as a starting point for a new one.'],
                            ['label' => 'Retry', 'desc' => 'If publishing fails, retry from the post detail page.'],
                        ],
                    ],
                ],
            ],

            'admin-billing' => [
                'slug' => 'admin-billing',
                'title' => 'Admin Billing Ledger',
                'icon' => 'ChartBarIcon',
                'color' => 'red',
                'sections' => [
                    [
                        'heading' => 'Overview',
                        'content' => 'The Admin Billing page provides a global view of all revenue generated by BizBoost services, including ad campaigns and messaging.',
                    ],
                    [
                        'heading' => 'Access',
                        'content' => 'This page is only available to admin users. Navigate to Admin → BizBoost Billing from the main sidebar.',
                    ],
                    [
                        'heading' => 'Dashboard Statistics',
                        'bullets' => [
                            ['label' => 'Gross Revenue', 'desc' => 'Total amount charged to all users across all services.'],
                            ['label' => 'Vendor Costs', 'desc' => 'Total amount paid to vendors (Meta, Twilio, etc.).'],
                            ['label' => 'Platform Profit', 'desc' => 'Gross Revenue minus Vendor Costs — your earnings.'],
                        ],
                    ],
                    [
                        'heading' => 'Service Breakdown',
                        'content' => 'Revenue and costs are broken down by service type:',
                        'bullets' => [
                            ['label' => 'Facebook Ads', 'desc' => 'Revenue from ad campaign launches.'],
                            ['label' => 'WhatsApp Messages', 'desc' => 'Per-message charges for WhatsApp delivery.'],
                            ['label' => 'SMS Messages', 'desc' => 'Per-message charges for SMS delivery.'],
                        ],
                    ],
                    [
                        'heading' => 'Transaction Search',
                        'content' => 'The ledger table supports searching by user email, service type, and date range. Each row shows the gross charge, vendor cost, platform profit, and delivery status.',
                    ],
                ],
            ],
        ];

        return $guides[$slug] ?? null;
    }
}
