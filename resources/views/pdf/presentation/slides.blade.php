<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MyGrowNet Platform Presentation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #111827;
        }
        .slide {
            page-break-after: always;
            width: 100%;
            height: 100vh;
            padding: 40px;
            position: relative;
        }
        .slide:last-child {
            page-break-after: avoid;
        }
        
        /* Gradient backgrounds */
        .bg-gradient-blue {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }
        .bg-gradient-green {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
        }
        .bg-gradient-purple {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            color: white;
        }
        .bg-white {
            background: white;
        }
        
        /* Typography */
        .slide-title {
            font-size: 36pt;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .slide-subtitle {
            font-size: 18pt;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 24pt;
            font-weight: bold;
            margin-bottom: 15px;
            color: #2563eb;
        }
        .text-lg {
            font-size: 14pt;
            line-height: 1.6;
        }
        
        /* Cards and boxes */
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card-title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2563eb;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #2563eb;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        
        /* Lists */
        ul {
            list-style: none;
            padding-left: 0;
        }
        li {
            padding: 8px 0;
            padding-left: 25px;
            position: relative;
        }
        li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #059669;
            font-weight: bold;
        }
        
        /* Footer */
        .slide-footer {
            position: absolute;
            bottom: 20px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 10pt;
            opacity: 0.7;
        }
        
        /* Grid layouts */
        .grid-2 {
            display: table;
            width: 100%;
        }
        .grid-col {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
        }
        .grid-3 {
            display: table;
            width: 100%;
        }
        .grid-col-3 {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            vertical-align: top;
        }
    </style>
</head>
<body>

<!-- Slide 1: Welcome -->
<div class="slide bg-gradient-blue">
    <div style="text-align: center; padding-top: 150px;">
        <div class="slide-title">Welcome to MyGrowNet</div>
        <div class="slide-subtitle">Access. Earn. Grow.</div>
        <p class="text-lg" style="margin-top: 40px;">
            Your gateway to financial empowerment and community growth
        </p>
    </div>
    <div class="slide-footer">MyGrowNet Platform | {{ $generatedAt }}</div>
</div>

<!-- Slide 2: About Us -->
<div class="slide bg-white">
    <div class="section-title">About MyGrowNet</div>
    <div class="text-lg" style="margin-top: 30px;">
        <p style="margin-bottom: 20px;">
            MyGrowNet is a community empowerment platform designed to equip members with practical life skills, 
            mentorship, and income opportunities through a sustainable 7-level community model.
        </p>
        
        <div class="grid-2" style="margin-top: 40px;">
            <div class="grid-col">
                <div class="card">
                    <div class="card-title">Learn</div>
                    <p>Skill-based training, coaching, and life improvement resources</p>
                </div>
            </div>
            <div class="grid-col">
                <div class="card">
                    <div class="card-title">Earn</div>
                    <p>Referrals, mentorship incentives, and level bonuses</p>
                </div>
            </div>
        </div>
        
        <div class="grid-2">
            <div class="grid-col">
                <div class="card">
                    <div class="card-title">Grow</div>
                    <p>Milestone rewards, booster funds, and business support</p>
                </div>
            </div>
            <div class="grid-col">
                <div class="card">
                    <div class="card-title">Legal & Compliant</div>
                    <p>Private Limited Company with full regulatory compliance</p>
                </div>
            </div>
        </div>
    </div>
    <div class="slide-footer">MyGrowNet Platform</div>
</div>

<!-- Slide 3: Starter Kits -->
<div class="slide bg-white">
    <div class="section-title">Starter Kit Options</div>
    <p class="text-lg">Choose the package that fits your goals</p>
    
    <table style="margin-top: 30px;">
        <thead>
            <tr>
                <th>Package</th>
                <th>Price</th>
                <th>Storage</th>
                <th>Earning Potential</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tiers as $tier)
            <tr>
                <td><strong>{{ $tier['name'] }}</strong></td>
                <td>K{{ number_format($tier['price'], 2) }}</td>
                <td>{{ $tier['storage_gb'] }} GB</td>
                <td>K{{ number_format($tier['earning_potential'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="slide-footer">MyGrowNet Platform</div>
</div>

<!-- Slide 4: How It Works -->
<div class="slide bg-white">
    <div class="section-title">How It Works</div>
    
    <div style="margin-top: 40px;">
        <div class="card">
            <div class="card-title">1. Choose Your Starter Kit</div>
            <p>Select a package that aligns with your goals and budget</p>
        </div>
        
        <div class="card">
            <div class="card-title">2. Access Platform Features</div>
            <p>Get instant access to learning resources, tools, and community</p>
        </div>
        
        <div class="card">
            <div class="card-title">3. Build Your Network</div>
            <p>Refer others and build your 3x7 matrix network</p>
        </div>
        
        <div class="card">
            <div class="card-title">4. Earn Multiple Income Streams</div>
            <p>Benefit from referral bonuses, level commissions, and community rewards</p>
        </div>
    </div>
    
    <div class="slide-footer">MyGrowNet Platform</div>
</div>

<!-- Slide 5: 7-Level Commission Structure -->
<div class="slide bg-white">
    <div class="section-title">7-Level Commission Structure</div>
    <p class="text-lg">Earn from 7 levels deep in your network</p>
    
    <table style="margin-top: 30px;">
        <thead>
            <tr>
                <th>Level</th>
                <th>Professional Title</th>
                <th>Commission Rate</th>
                <th>Network Positions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commissionRates as $rate)
            <tr>
                <td><strong>Level {{ $rate['level'] }}</strong></td>
                <td>{{ $rate['name'] }}</td>
                <td>{{ $rate['rate'] }}%</td>
                <td>{{ number_format($rate['positions']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="slide-footer">MyGrowNet Platform</div>
</div>

<!-- Slide 6: Matrix System -->
<div class="slide bg-gradient-purple">
    <div class="slide-title">3x7 Forced Matrix System</div>
    <div class="slide-subtitle">Build a powerful network with spillover benefits</div>
    
    <div style="margin-top: 50px;">
        <div class="card">
            <div class="card-title">Matrix Configuration</div>
            <div class="grid-2">
                <div class="grid-col">
                    <p><strong>Width:</strong> {{ $matrix['width'] }} positions per level</p>
                    <p><strong>Depth:</strong> {{ $matrix['depth'] }} levels deep</p>
                </div>
                <div class="grid-col">
                    <p><strong>Total Capacity:</strong> {{ number_format($matrix['total_capacity']) }} members</p>
                    <p><strong>Spillover:</strong> Automatic placement for growth</p>
                </div>
            </div>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <div class="card-title">Benefits</div>
            <ul>
                <li>Automatic spillover from your upline</li>
                <li>Earn from all 7 levels in your matrix</li>
                <li>Fair distribution of new members</li>
                <li>Passive income potential</li>
            </ul>
        </div>
    </div>
    
    <div class="slide-footer">MyGrowNet Platform</div>
</div>

<!-- Slide 7: Performance Tiers -->
<div class="slide bg-white">
    <div class="section-title">Performance Bonus Tiers</div>
    <p class="text-lg">Earn extra bonuses based on your monthly activity</p>
    
    <table style="margin-top: 30px;">
        <thead>
            <tr>
                <th>Tier</th>
                <th>Monthly Points Required</th>
                <th>Bonus Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($performanceTiers as $tier)
            <tr>
                <td><strong>{{ $tier['name'] }}</strong></td>
                <td>{{ number_format($tier['points']) }} MAP</td>
                <td>+{{ $tier['bonus'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="card" style="margin-top: 30px;">
        <p><strong>Note:</strong> Monthly Activity Points (MAP) reset on the 1st of each month. 
        Stay active to maintain your performance tier and maximize earnings!</p>
    </div>
    
    <div class="slide-footer">MyGrowNet Platform</div>
</div>

<!-- Slide 8: Community Rewards -->
<div class="slide bg-gradient-green">
    <div class="slide-title">Community Rewards</div>
    <div class="slide-subtitle">Share in the success of community projects</div>
    
    <div style="margin-top: 50px;">
        <div class="card">
            <div class="card-title">How It Works</div>
            <p style="margin-bottom: 15px;">
                MyGrowNet invests in profitable community projects (agriculture, manufacturing, services, real estate). 
                All active members receive quarterly profit distributions.
            </p>
            
            <div class="grid-2" style="margin-top: 20px;">
                <div class="grid-col">
                    <p><strong>60% of project profits</strong></p>
                    <p>Distributed to active members</p>
                </div>
                <div class="grid-col">
                    <p><strong>40% retained</strong></p>
                    <p>For operations and growth</p>
                </div>
            </div>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <div class="card-title">Distribution Structure</div>
            <ul>
                <li>50% equal share among all active members</li>
                <li>50% weighted by professional level</li>
                <li>Quarterly distributions</li>
                <li>Must be active to qualify</li>
            </ul>
        </div>
    </div>
    
    <div class="slide-footer">MyGrowNet Platform</div>
</div>

<!-- Slide 9: Join Now -->
<div class="slide bg-gradient-blue">
    <div style="text-align: center; padding-top: 120px;">
        <div class="slide-title">Ready to Get Started?</div>
        <div class="slide-subtitle">Join MyGrowNet Today</div>
        
        <div style="margin-top: 60px;">
            <div class="card" style="display: inline-block; text-align: left; max-width: 600px;">
                <div class="card-title" style="text-align: center; font-size: 20pt;">Next Steps</div>
                <ul style="margin-top: 20px;">
                    <li>Visit our website or contact your referrer</li>
                    <li>Choose your starter kit package</li>
                    <li>Complete registration and payment</li>
                    <li>Access your dashboard and start earning</li>
                </ul>
            </div>
        </div>
        
        <p class="text-lg" style="margin-top: 40px; opacity: 0.9;">
            Questions? Contact our support team anytime
        </p>
    </div>
    
    <div class="slide-footer">MyGrowNet Platform | Thank You!</div>
</div>

</body>
</html>
