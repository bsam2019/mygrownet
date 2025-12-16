import PptxGenJS from 'pptxgenjs';

interface ReferralLevel {
    level: number;
    team_size: number;
    commission_percentage: number;
    potential_earnings: number;
    per_person: number;
}

interface PositionBonus {
    position: string;
    teamSize: number;
    cumulative: number;
    lpEstimate: number;
    milestoneBonus: string;
    physicalReward: string | null;
}

interface CompensationPlanData {
    registrationAmount: number;
    referralBonusStructure: ReferralLevel[];
    totalPotential: number;
    totalTeamSize: number;
    levelNames: Record<number, string>;
    positionBonuses: PositionBonus[];
}

export function useCompensationPlanPPT() {
    const formatNumber = (num: number) => new Intl.NumberFormat('en-US').format(num);

    const generatePresentation = async (data: CompensationPlanData) => {
        const pptx = new PptxGenJS();

        // Set presentation properties
        pptx.author = 'MyGrowNet';
        pptx.title = 'MyGrowNet Compensation Plan';
        pptx.subject = 'Compensation Plan Presentation';
        pptx.company = 'MyGrowNet';

        // Define colors
        const colors = {
            primary: '2563eb',
            primaryDark: '1d4ed8',
            emerald: '059669',
            purple: '7c3aed',
            indigo: '4f46e5',
            gray: '6b7280',
            white: 'FFFFFF',
            lightGray: 'f3f4f6',
        };

        // Slide 1: Title Slide
        const slide1 = pptx.addSlide();
        slide1.addShape(pptx.ShapeType.rect, {
            x: 0,
            y: 0,
            w: '100%',
            h: '100%',
            fill: { type: 'solid', color: colors.primary },
        });
        slide1.addText('MyGrowNet', {
            x: 0.5,
            y: 1.5,
            w: '90%',
            h: 1,
            fontSize: 48,
            bold: true,
            color: colors.white,
            align: 'center',
        });
        slide1.addText('Compensation Plan', {
            x: 0.5,
            y: 2.5,
            w: '90%',
            h: 0.8,
            fontSize: 36,
            color: colors.white,
            align: 'center',
        });
        slide1.addText('Build your network and earn through our 7-level referral bonus system', {
            x: 0.5,
            y: 3.5,
            w: '90%',
            h: 0.6,
            fontSize: 18,
            color: colors.white,
            align: 'center',
        });

        // Slide 2: Registration Amount
        const slide2 = pptx.addSlide();
        slide2.addText('Registration Amount', {
            x: 0.5,
            y: 0.3,
            w: '90%',
            h: 0.6,
            fontSize: 32,
            bold: true,
            color: colors.primaryDark,
        });
        slide2.addShape(pptx.ShapeType.roundRect, {
            x: 1,
            y: 1.2,
            w: 8,
            h: 2.5,
            fill: { type: 'solid', color: colors.primary },
            rectRadius: 0.2,
        });
        slide2.addText(`K${formatNumber(data.registrationAmount)}`, {
            x: 1,
            y: 1.5,
            w: 8,
            h: 1.2,
            fontSize: 56,
            bold: true,
            color: colors.white,
            align: 'center',
        });
        slide2.addText('One-time registration fee that unlocks your earning potential', {
            x: 1,
            y: 2.8,
            w: 8,
            h: 0.5,
            fontSize: 16,
            color: colors.white,
            align: 'center',
        });

        // Slide 3: 7-Level Referral Bonus Structure
        const slide3 = pptx.addSlide();
        slide3.addText('7-Level Referral Bonus Structure', {
            x: 0.5,
            y: 0.3,
            w: '90%',
            h: 0.6,
            fontSize: 28,
            bold: true,
            color: colors.emerald,
        });

        // Table header
        const tableRows: PptxGenJS.TableRow[] = [
            [
                { text: 'Level', options: { bold: true, fill: { color: colors.emerald }, color: colors.white } },
                { text: 'Team Size', options: { bold: true, fill: { color: colors.emerald }, color: colors.white } },
                { text: 'Commission %', options: { bold: true, fill: { color: colors.emerald }, color: colors.white } },
                { text: 'Cash Value', options: { bold: true, fill: { color: colors.emerald }, color: colors.white } },
                { text: 'BP Earned', options: { bold: true, fill: { color: colors.emerald }, color: colors.white } },
            ],
        ];

        // Table data
        data.referralBonusStructure.forEach((level) => {
            tableRows.push([
                { text: `Level ${level.level}`, options: { align: 'center' } },
                { text: formatNumber(level.team_size), options: { align: 'right' } },
                { text: `${level.commission_percentage}%`, options: { align: 'center', color: colors.emerald } },
                { text: `K${formatNumber(level.per_person)}`, options: { align: 'right' } },
                { text: `${(level.per_person / 2).toFixed(1)} BP`, options: { align: 'right', color: colors.indigo } },
            ]);
        });

        // Total row
        tableRows.push([
            { text: 'TOTAL', options: { bold: true, fill: { color: colors.lightGray } } },
            { text: formatNumber(data.totalTeamSize), options: { bold: true, fill: { color: colors.lightGray }, align: 'right' } },
            { text: '48%', options: { bold: true, fill: { color: colors.lightGray }, align: 'center' } },
            { text: `K${formatNumber(data.totalPotential)}`, options: { bold: true, fill: { color: colors.lightGray }, align: 'right' } },
            { text: `${formatNumber(Math.round(data.totalPotential / 2))} BP`, options: { bold: true, fill: { color: colors.lightGray }, align: 'right', color: colors.emerald } },
        ]);

        slide3.addTable(tableRows, {
            x: 0.5,
            y: 1,
            w: 9,
            colW: [1.5, 1.8, 1.8, 1.8, 2.1],
            fontSize: 12,
            border: { pt: 0.5, color: 'CCCCCC' },
            align: 'center',
            valign: 'middle',
        });

        // Slide 4: Position Bonus (Milestone Rewards)
        const slide4 = pptx.addSlide();
        slide4.addText('Position Bonus (Milestone Rewards)', {
            x: 0.5,
            y: 0.3,
            w: '90%',
            h: 0.6,
            fontSize: 28,
            bold: true,
            color: colors.purple,
        });
        slide4.addText('Bonus pool distribution based on team performance', {
            x: 0.5,
            y: 0.8,
            w: '90%',
            h: 0.4,
            fontSize: 14,
            color: colors.gray,
        });

        const positionTableRows: PptxGenJS.TableRow[] = [
            [
                { text: 'Position', options: { bold: true, fill: { color: colors.purple }, color: colors.white } },
                { text: 'Team Size', options: { bold: true, fill: { color: colors.purple }, color: colors.white } },
                { text: 'Cumulative', options: { bold: true, fill: { color: colors.purple }, color: colors.white } },
                { text: 'LP Estimate', options: { bold: true, fill: { color: colors.purple }, color: colors.white } },
                { text: 'Milestone Bonus', options: { bold: true, fill: { color: colors.purple }, color: colors.white } },
            ],
        ];

        data.positionBonuses.forEach((bonus) => {
            positionTableRows.push([
                { text: bonus.position, options: { align: 'left', color: colors.purple } },
                { text: formatNumber(bonus.teamSize), options: { align: 'right' } },
                { text: formatNumber(bonus.cumulative), options: { align: 'right' } },
                { text: `${formatNumber(bonus.lpEstimate)} LP`, options: { align: 'right', color: colors.indigo } },
                { text: bonus.milestoneBonus, options: { align: 'right', color: colors.emerald } },
            ]);
        });

        slide4.addTable(positionTableRows, {
            x: 0.5,
            y: 1.3,
            w: 9,
            colW: [2, 1.5, 1.5, 2, 2],
            fontSize: 11,
            border: { pt: 0.5, color: 'CCCCCC' },
            align: 'center',
            valign: 'middle',
        });

        // Slide 5: How It Works
        const slide5 = pptx.addSlide();
        slide5.addText('How It Works', {
            x: 0.5,
            y: 0.3,
            w: '90%',
            h: 0.6,
            fontSize: 32,
            bold: true,
            color: colors.primaryDark,
        });

        const howItWorksSteps = [
            {
                title: '1. Registration Commission (Converted to BP)',
                desc: 'When someone in your network pays the K500 registration fee, you earn a commission percentage that\'s converted to BP (Bonus Points) at K2 per BP.',
            },
            {
                title: '2. 7 Levels Deep (BP Earnings)',
                desc: 'Level 1: 15% × K500 = K75 → 37.5 BP | Level 2: 10% × K500 = K50 → 25 BP | Level 3: 8% × K500 = K40 → 20 BP | And so on through 7 levels!',
            },
            {
                title: '3. Maximum BP Potential',
                desc: `If you fill all ${formatNumber(data.totalTeamSize)} positions in your 7-level network, you could earn ${formatNumber(Math.round(data.totalPotential / 2))} BP (cash value: K${formatNumber(data.totalPotential)}) in referral bonuses!`,
            },
        ];

        let yPos = 1.1;
        howItWorksSteps.forEach((step) => {
            slide5.addText(step.title, {
                x: 0.5,
                y: yPos,
                w: 9,
                h: 0.4,
                fontSize: 16,
                bold: true,
                color: colors.primary,
            });
            slide5.addText(step.desc, {
                x: 0.5,
                y: yPos + 0.4,
                w: 9,
                h: 0.8,
                fontSize: 12,
                color: colors.gray,
            });
            yPos += 1.4;
        });

        // Slide 6: 6 Income Streams Overview
        const slide6 = pptx.addSlide();
        slide6.addText('6 Powerful Income Streams', {
            x: 0.5,
            y: 0.3,
            w: '90%',
            h: 0.6,
            fontSize: 32,
            bold: true,
            color: colors.primaryDark,
        });

        const incomeStreams = [
            { num: '1', title: 'Monthly Bonus Pool', desc: '60% of monthly company profits distributed based on your BP share' },
            { num: '2', title: 'Referral Commissions', desc: 'Earn BP when you refer new members (37.5 BP per direct referral)' },
            { num: '3', title: 'Network Commissions', desc: 'Earn from your entire network across 7 professional levels' },
            { num: '4', title: 'Product Sales Commissions', desc: 'Earn from MyGrow Shop sales (10-20 BP per K100 sold)' },
            { num: '5', title: 'Quarterly Profit-Sharing', desc: '60% of investment project profits shared with ALL active members' },
            { num: '6', title: 'Milestone Rewards', desc: 'Earn bonuses as you advance through professional levels' },
        ];

        let streamY = 1;
        incomeStreams.forEach((stream, index) => {
            const col = index % 2;
            const row = Math.floor(index / 2);
            const x = col === 0 ? 0.5 : 5;
            const y = streamY + row * 1.5;

            slide6.addShape(pptx.ShapeType.ellipse, {
                x: x,
                y: y,
                w: 0.5,
                h: 0.5,
                fill: { type: 'solid', color: colors.primary },
            });
            slide6.addText(stream.num, {
                x: x,
                y: y + 0.05,
                w: 0.5,
                h: 0.4,
                fontSize: 16,
                bold: true,
                color: colors.white,
                align: 'center',
            });
            slide6.addText(stream.title, {
                x: x + 0.6,
                y: y,
                w: 4,
                h: 0.4,
                fontSize: 14,
                bold: true,
                color: colors.primaryDark,
            });
            slide6.addText(stream.desc, {
                x: x + 0.6,
                y: y + 0.4,
                w: 4,
                h: 0.8,
                fontSize: 10,
                color: colors.gray,
            });
        });

        // Slide 7: Monthly Bonus Pool Details
        const slide7 = pptx.addSlide();
        slide7.addText('Income Stream #1: Monthly Bonus Pool', {
            x: 0.5,
            y: 0.3,
            w: '90%',
            h: 0.6,
            fontSize: 28,
            bold: true,
            color: colors.primary,
        });

        slide7.addShape(pptx.ShapeType.roundRect, {
            x: 0.5,
            y: 1,
            w: 9,
            h: 1.2,
            fill: { type: 'solid', color: 'EBF5FF' },
            rectRadius: 0.1,
            line: { color: colors.primary, pt: 2 },
        });
        slide7.addText('60% of monthly company profits distributed based on your BP share!', {
            x: 0.7,
            y: 1.1,
            w: 8.6,
            h: 0.5,
            fontSize: 16,
            bold: true,
            color: colors.primaryDark,
        });
        slide7.addText('Formula: Your Bonus = (Your BP ÷ Total BP) × 60% of Monthly Profit', {
            x: 0.7,
            y: 1.6,
            w: 8.6,
            h: 0.4,
            fontSize: 12,
            color: colors.gray,
        });

        slide7.addText('How You Earn BP:', {
            x: 0.5,
            y: 2.4,
            w: 4,
            h: 0.4,
            fontSize: 14,
            bold: true,
            color: colors.primaryDark,
        });

        const bpEarnings = [
            'Referrals: 37.5 BP per Level 1 referral',
            'Product Sales: 10-20 BP per K100 sold',
            'Courses: 30-100 BP per completion',
            'Daily Login: 5 BP/day, 50 BP/week streak',
            'Subscription Renewal: 25 BP/month',
        ];

        bpEarnings.forEach((item, index) => {
            slide7.addText(`• ${item}`, {
                x: 0.7,
                y: 2.8 + index * 0.35,
                w: 8,
                h: 0.35,
                fontSize: 11,
                color: colors.gray,
            });
        });

        // Slide 8: Quarterly Profit-Sharing
        const slide8 = pptx.addSlide();
        slide8.addText('Income Stream #5: Quarterly Profit-Sharing', {
            x: 0.5,
            y: 0.3,
            w: '90%',
            h: 0.6,
            fontSize: 28,
            bold: true,
            color: colors.emerald,
        });

        slide8.addShape(pptx.ShapeType.roundRect, {
            x: 0.5,
            y: 1,
            w: 9,
            h: 1.5,
            fill: { type: 'solid', color: 'ECFDF5' },
            rectRadius: 0.1,
            line: { color: colors.emerald, pt: 2 },
        });
        slide8.addText('60% of investment project profits shared with ALL active members!', {
            x: 0.7,
            y: 1.1,
            w: 8.6,
            h: 0.5,
            fontSize: 16,
            bold: true,
            color: colors.emerald,
        });
        slide8.addText('MyGrowNet invests in profitable community projects (agriculture, manufacturing, real estate, services). Every active member receives a share of the profits.', {
            x: 0.7,
            y: 1.6,
            w: 8.6,
            h: 0.7,
            fontSize: 11,
            color: colors.gray,
        });

        slide8.addText('Distribution Structure:', {
            x: 0.5,
            y: 2.7,
            w: 4,
            h: 0.4,
            fontSize: 14,
            bold: true,
            color: colors.primaryDark,
        });

        const profitSharing = [
            '50% Equal Share: All active members receive equal portion',
            '50% Weighted Share: Based on professional level (Associate: 1.0x → Ambassador: 4.0x)',
            'Paid Quarterly: Every 3 months',
            'Passive Income: Grows with company investments',
        ];

        profitSharing.forEach((item, index) => {
            slide8.addText(`• ${item}`, {
                x: 0.7,
                y: 3.1 + index * 0.35,
                w: 8,
                h: 0.35,
                fontSize: 11,
                color: colors.gray,
            });
        });

        // Slide 9: 7 Professional Levels
        const slide9 = pptx.addSlide();
        slide9.addText('7 Professional Levels', {
            x: 0.5,
            y: 0.3,
            w: '90%',
            h: 0.6,
            fontSize: 28,
            bold: true,
            color: colors.indigo,
        });

        const levels = [
            { name: 'Associate', team: '3', lp: '0', multiplier: '1.0x' },
            { name: 'Professional', team: '12', lp: '2,500', multiplier: '1.5x' },
            { name: 'Senior', team: '39', lp: '4,000', multiplier: '2.0x' },
            { name: 'Manager', team: '120', lp: '12,500', multiplier: '2.5x' },
            { name: 'Director', team: '363', lp: '60,000', multiplier: '3.0x' },
            { name: 'Executive', team: '1,092', lp: '160,000', multiplier: '3.5x' },
            { name: 'Ambassador', team: '3,279', lp: '350,000', multiplier: '4.0x' },
        ];

        const levelTableRows: PptxGenJS.TableRow[] = [
            [
                { text: 'Level', options: { bold: true, fill: { color: colors.indigo }, color: colors.white } },
                { text: 'Team Size', options: { bold: true, fill: { color: colors.indigo }, color: colors.white } },
                { text: 'LP Required', options: { bold: true, fill: { color: colors.indigo }, color: colors.white } },
                { text: 'Profit Multiplier', options: { bold: true, fill: { color: colors.indigo }, color: colors.white } },
            ],
        ];

        levels.forEach((level) => {
            levelTableRows.push([
                { text: level.name, options: { align: 'left', color: colors.indigo } },
                { text: level.team, options: { align: 'right' } },
                { text: `${level.lp} LP`, options: { align: 'right' } },
                { text: level.multiplier, options: { align: 'center', color: colors.emerald } },
            ]);
        });

        slide9.addTable(levelTableRows, {
            x: 0.5,
            y: 1,
            w: 9,
            colW: [2.5, 2, 2.5, 2],
            fontSize: 12,
            border: { pt: 0.5, color: 'CCCCCC' },
            align: 'center',
            valign: 'middle',
        });

        // Slide 10: Getting Started
        const slide10 = pptx.addSlide();
        slide10.addShape(pptx.ShapeType.rect, {
            x: 0,
            y: 0,
            w: '100%',
            h: '100%',
            fill: { type: 'solid', color: colors.primary },
        });
        slide10.addText('Ready to Start Earning?', {
            x: 0.5,
            y: 1,
            w: '90%',
            h: 0.8,
            fontSize: 36,
            bold: true,
            color: colors.white,
            align: 'center',
        });

        const steps = [
            '1. Register with K500 one-time fee',
            '2. Complete your profile and training',
            '3. Start referring friends and family',
            '4. Build your 7-level network',
            '5. Earn from 6 income streams!',
        ];

        steps.forEach((step, index) => {
            slide10.addText(step, {
                x: 1.5,
                y: 2 + index * 0.5,
                w: 7,
                h: 0.5,
                fontSize: 18,
                color: colors.white,
            });
        });

        slide10.addText('Join MyGrowNet Today!', {
            x: 0.5,
            y: 4.7,
            w: '90%',
            h: 0.6,
            fontSize: 24,
            bold: true,
            color: colors.white,
            align: 'center',
        });

        // Generate and download
        await pptx.writeFile({ fileName: 'MyGrowNet_Compensation_Plan.pptx' });
    };

    return {
        generatePresentation,
    };
}