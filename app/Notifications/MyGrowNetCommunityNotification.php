<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MyGrowNetCommunityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public array $data
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        if (config('mygrownet.notifications.email_enabled', true)) {
            $channels[] = 'mail';
        }
        
        if (config('mygrownet.notifications.sms_enabled', true) && $notifiable->phone) {
            // SMS channel will be added when SMS service is implemented
            // $channels[] = 'sms';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return match ($this->data['type']) {
            'project_launched' => $this->projectLaunchedMail($notifiable),
            'project_update_posted' => $this->projectUpdatePostedMail($notifiable),
            'project_milestone_reached' => $this->projectMilestoneReachedMail($notifiable),
            'project_funding_target_reached' => $this->projectFundingTargetReachedMail($notifiable),
            'project_completed' => $this->projectCompletedMail($notifiable),
            'voting_opened' => $this->votingOpenedMail($notifiable),
            'voting_reminder' => $this->votingReminderMail($notifiable),
            'voting_closing_soon' => $this->votingClosingSoonMail($notifiable),
            'voting_results' => $this->votingResultsMail($notifiable),
            'profit_distribution_calculated' => $this->profitDistributionCalculatedMail($notifiable),
            'profit_distribution_paid' => $this->profitDistributionPaidMail($notifiable),
            'quarterly_community_report' => $this->quarterlyCommunityReportMail($notifiable),
            'new_project_opportunity' => $this->newProjectOpportunityMail($notifiable),
            'contribution_acknowledged' => $this->contributionAcknowledgedMail($notifiable),
            'project_risk_alert' => $this->projectRiskAlertMail($notifiable),
            default => $this->defaultMail($notifiable)
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->data['type'],
            'message' => $this->getDatabaseMessage(),
            'data' => $this->data,
            'priority' => $this->getNotificationPriority()
        ];
    }

    /**
     * Project launched notification
     */
    protected function projectLaunchedMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $projectType = $this->data['project_type'] ?? 'Community Project';
        $targetAmount = number_format($this->data['target_amount'] ?? 0, 2);
        $minimumContribution = number_format($this->data['minimum_contribution'] ?? 0, 2);
        $projectDescription = $this->data['project_description'] ?? '';
        $expectedReturns = $this->data['expected_returns'] ?? '';
        $projectDuration = $this->data['project_duration'] ?? '';
        
        return (new MailMessage)
            ->subject("🚀 New Community Project Launched: {$projectName}")
            ->greeting("Exciting opportunity, {$notifiable->name}!")
            ->line("A new community project has been launched and is now open for contributions from MyGrowNet members!")
            ->line("**Project Details:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Type:** {$projectType}")
            ->line("• **Target Amount:** K{$targetAmount}")
            ->line("• **Minimum Contribution:** K{$minimumContribution}")
            ->when($projectDuration, fn($mail) => $mail->line("• **Duration:** {$projectDuration}"))
            ->when($expectedReturns, fn($mail) => $mail->line("• **Expected Returns:** {$expectedReturns}"))
            ->when($projectDescription, function ($mail) use ($projectDescription) {
                return $mail->line("**Project Description:**")
                    ->line($projectDescription);
            })
            ->line("**Community Investment Opportunity:**")
            ->line("This project represents a chance for our community to invest together and share in the profits. Your contribution helps build collective wealth for all participants.")
            ->line("**How It Works:**")
            ->line("• Make your contribution to join the project")
            ->line("• Participate in project governance through voting")
            ->line("• Share in profits based on your contribution percentage")
            ->line("• Receive regular updates on project progress")
            ->line("**Get Involved:**")
            ->line("Review the project details and consider making a contribution. Every member's participation strengthens our community investment power!")
            ->action('View Project Details', url('/mygrownet/dashboard'))
            ->line('Together, we build wealth for everyone!')
            ->salutation('Building community wealth, The MyGrowNet Team');
    }

    /**
     * Project update posted notification
     */
    protected function projectUpdatePostedMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $updateTitle = $this->data['update_title'] ?? 'Project Update';
        $updateSummary = $this->data['update_summary'] ?? '';
        $progressPercentage = $this->data['progress_percentage'] ?? 0;
        $currentAmount = number_format($this->data['current_amount'] ?? 0, 2);
        $targetAmount = number_format($this->data['target_amount'] ?? 0, 2);
        $updateDate = $this->data['update_date'] ?? date('Y-m-d');
        
        return (new MailMessage)
            ->subject("📈 Project Update: {$projectName}")
            ->greeting("Project update, {$notifiable->name}!")
            ->line("There's a new update for the {$projectName} community project you're participating in.")
            ->line("**Update Details:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Update:** {$updateTitle}")
            ->line("• **Date:** {$updateDate}")
            ->line("• **Progress:** {$progressPercentage}% complete")
            ->line("• **Current Funding:** K{$currentAmount} of K{$targetAmount}")
            ->when($updateSummary, function ($mail) use ($updateSummary) {
                return $mail->line("**Update Summary:**")
                    ->line($updateSummary);
            })
            ->line("**Your Investment is Working:**")
            ->line("This update shows the progress being made with your community investment. Transparency and regular communication are key to our collective success.")
            ->line("**Stay Engaged:**")
            ->line("• Review the full update details")
            ->line("• Participate in any upcoming votes")
            ->line("• Share feedback with the project team")
            ->action('Read Full Update', url('/mygrownet/dashboard'))
            ->line('Your investment is making a difference!')
            ->salutation('Keeping you informed, The MyGrowNet Team');
    }

    /**
     * Project milestone reached notification
     */
    protected function projectMilestoneReachedMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $milestoneName = $this->data['milestone_name'];
        $milestoneDescription = $this->data['milestone_description'] ?? '';
        $progressPercentage = $this->data['progress_percentage'] ?? 0;
        $nextMilestone = $this->data['next_milestone'] ?? '';
        $celebrationBonus = $this->data['celebration_bonus'] ?? '';
        
        return (new MailMessage)
            ->subject("🎯 Milestone Achieved: {$projectName}")
            ->greeting("Celebration time, {$notifiable->name}!")
            ->line("Great news! The {$projectName} community project has reached an important milestone!")
            ->line("**Milestone Achievement:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Milestone:** {$milestoneName}")
            ->line("• **Progress:** {$progressPercentage}% complete")
            ->when($milestoneDescription, fn($mail) => $mail->line("• **Description:** {$milestoneDescription}"))
            ->when($nextMilestone, fn($mail) => $mail->line("• **Next Milestone:** {$nextMilestone}"))
            ->when($celebrationBonus, fn($mail) => $mail->line("• **Celebration Bonus:** {$celebrationBonus}"))
            ->line("**Community Success:**")
            ->line("This milestone represents the collective effort and investment of our MyGrowNet community. Your contribution is part of this success!")
            ->line("**What This Means:**")
            ->line("• Project is progressing as planned")
            ->line("• Your investment is being put to good use")
            ->line("• We're moving closer to profit distribution")
            ->line("• Community collaboration is working!")
            ->line("**Keep the Momentum:**")
            ->line("Continue following project updates and participating in community decisions. Together, we're building something great!")
            ->action('View Project Progress', url('/mygrownet/dashboard'))
            ->line('Celebrating our collective success!')
            ->salutation('Proud of our community, The MyGrowNet Team');
    }

    /**
     * Project funding target reached notification
     */
    protected function projectFundingTargetReachedMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $targetAmount = number_format($this->data['target_amount'] ?? 0, 2);
        $totalContributors = $this->data['total_contributors'] ?? 0;
        $userContribution = number_format($this->data['user_contribution'] ?? 0, 2);
        $userPercentage = $this->data['user_percentage'] ?? 0;
        $projectStartDate = $this->data['project_start_date'] ?? '';
        
        return (new MailMessage)
            ->subject("🎉 Funding Complete: {$projectName}")
            ->greeting("Fantastic news, {$notifiable->name}!")
            ->line("The {$projectName} community project has reached its full funding target! The project is now ready to begin execution.")
            ->line("**Funding Success:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Target Reached:** K{$targetAmount}")
            ->line("• **Total Contributors:** {$totalContributors} community members")
            ->line("• **Your Contribution:** K{$userContribution} ({$userPercentage}%)")
            ->when($projectStartDate, fn($mail) => $mail->line("• **Project Start Date:** {$projectStartDate}"))
            ->line("**Community Achievement:**")
            ->line("This is a testament to the power of our MyGrowNet community! Together, we've raised K{$targetAmount} to fund this exciting project.")
            ->line("**Your Investment Share:**")
            ->line("With your K{$userContribution} contribution, you own {$userPercentage}% of this project and will receive {$userPercentage}% of all profits generated.")
            ->line("**What Happens Next:**")
            ->line("• Project execution begins immediately")
            ->line("• Regular progress updates will be provided")
            ->line("• You'll participate in key project decisions")
            ->line("• Profit distributions will begin once the project generates returns")
            ->line("**Thank You:**")
            ->line("Your participation made this project possible. This is community investment at its finest!")
            ->action('Track Project Progress', url('/mygrownet/dashboard'))
            ->line('Here\'s to our collective success!')
            ->salutation('Grateful for your participation, The MyGrowNet Team');
    }

    /**
     * Project completed notification
     */
    protected function projectCompletedMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $projectDuration = $this->data['project_duration'] ?? '';
        $totalProfit = number_format($this->data['total_profit'] ?? 0, 2);
        $userShare = number_format($this->data['user_share'] ?? 0, 2);
        $returnPercentage = $this->data['return_percentage'] ?? 0;
        $completionDate = $this->data['completion_date'] ?? date('Y-m-d');
        $paymentDate = $this->data['payment_date'] ?? '';
        
        return (new MailMessage)
            ->subject("🏆 Project Completed: {$projectName}")
            ->greeting("Success achieved, {$notifiable->name}!")
            ->line("Excellent news! The {$projectName} community project has been successfully completed!")
            ->line("**Project Completion:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Completion Date:** {$completionDate}")
            ->when($projectDuration, fn($mail) => $mail->line("• **Duration:** {$projectDuration}"))
            ->line("• **Total Profit Generated:** K{$totalProfit}")
            ->line("• **Your Profit Share:** K{$userShare}")
            ->line("• **Return on Investment:** {$returnPercentage}%")
            ->when($paymentDate, fn($mail) => $mail->line("• **Payment Date:** {$paymentDate}"))
            ->line("**Outstanding Results:**")
            ->line("This project has delivered excellent returns for all community participants. Your investment has not only grown but also contributed to the success of our collective wealth-building efforts.")
            ->line("**Community Impact:**")
            ->line("• Total community profit: K{$totalProfit}")
            ->line("• Successful collaboration among members")
            ->line("• Proven model for future projects")
            ->line("• Strengthened community investment power")
            ->line("**Your Success:**")
            ->line("Your K{$userShare} profit share demonstrates the power of community investing. This is wealth building in action!")
            ->line("**What's Next:**")
            ->line("• Your profit will be distributed shortly")
            ->line("• Look out for new project opportunities")
            ->line("• Consider reinvesting in future community projects")
            ->action('View Project Results', url('/mygrownet/dashboard'))
            ->line('Congratulations on this investment success!')
            ->salutation('Celebrating our achievement, The MyGrowNet Team');
    }

    /**
     * Voting opened notification
     */
    protected function votingOpenedMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $votingTopic = $this->data['voting_topic'];
        $votingDescription = $this->data['voting_description'] ?? '';
        $votingDeadline = $this->data['voting_deadline'] ?? '';
        $votingOptions = $this->data['voting_options'] ?? [];
        $userVotingPower = $this->data['user_voting_power'] ?? 0;
        
        return (new MailMessage)
            ->subject("🗳️ Community Vote: {$projectName}")
            ->greeting("Your voice matters, {$notifiable->name}!")
            ->line("A new community vote has been opened for the {$projectName} project. Your participation in project governance is important!")
            ->line("**Voting Details:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Topic:** {$votingTopic}")
            ->when($votingDeadline, fn($mail) => $mail->line("• **Voting Deadline:** {$votingDeadline}"))
            ->line("• **Your Voting Power:** {$userVotingPower}%")
            ->when($votingDescription, function ($mail) use ($votingDescription) {
                return $mail->line("**Voting Description:**")
                    ->line($votingDescription);
            })
            ->when(!empty($votingOptions), function ($mail) use ($votingOptions) {
                $mail->line("**Voting Options:**");
                foreach ($votingOptions as $option) {
                    $mail->line("• {$option}");
                }
                return $mail;
            })
            ->line("**Why Your Vote Matters:**")
            ->line("As a project contributor, you have a say in important project decisions. Your voting power is based on your contribution percentage, ensuring fair representation.")
            ->line("**Community Governance:**")
            ->line("• Democratic decision-making process")
            ->line("• Transparent voting system")
            ->line("• All contributors have a voice")
            ->line("• Decisions benefit the entire community")
            ->line("**Take Action:**")
            ->line("Review the voting details carefully and cast your vote before the deadline. Every vote counts in our community democracy!")
            ->action('Cast Your Vote', url('/mygrownet/dashboard'))
            ->line('Your participation shapes our community!')
            ->salutation('Empowering your voice, The MyGrowNet Team');
    }

    /**
     * Voting reminder notification
     */
    protected function votingReminderMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $votingTopic = $this->data['voting_topic'];
        $votingDeadline = $this->data['voting_deadline'] ?? '';
        $timeRemaining = $this->data['time_remaining'] ?? '';
        $currentParticipation = $this->data['current_participation'] ?? 0;
        
        return (new MailMessage)
            ->subject("⏰ Voting Reminder: {$projectName}")
            ->greeting("Reminder, {$notifiable->name}!")
            ->line("This is a friendly reminder that voting is still open for the {$projectName} project, and we haven't received your vote yet.")
            ->line("**Voting Details:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Topic:** {$votingTopic}")
            ->when($votingDeadline, fn($mail) => $mail->line("• **Deadline:** {$votingDeadline}"))
            ->when($timeRemaining, fn($mail) => $mail->line("• **Time Remaining:** {$timeRemaining}"))
            ->line("• **Current Participation:** {$currentParticipation}% of contributors have voted")
            ->line("**Your Voice is Important:**")
            ->line("As a project contributor, your input helps shape the direction of our community investment. Don't miss this opportunity to influence the project's future!")
            ->line("**Easy Voting Process:**")
            ->line("• Review the voting options")
            ->line("• Consider the impact on the project")
            ->line("• Cast your vote with one click")
            ->line("• See real-time voting results")
            ->line("**Community Democracy:**")
            ->line("Our community thrives on participation. The more members who vote, the stronger our collective decision-making becomes.")
            ->action('Vote Now', url('/mygrownet/dashboard'))
            ->line('Every vote strengthens our community!')
            ->salutation('Encouraging participation, The MyGrowNet Team');
    }

    /**
     * Voting closing soon notification
     */
    protected function votingClosingSoonMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $votingTopic = $this->data['voting_topic'];
        $hoursRemaining = $this->data['hours_remaining'] ?? 24;
        $currentParticipation = $this->data['current_participation'] ?? 0;
        $leadingOption = $this->data['leading_option'] ?? '';
        
        return (new MailMessage)
            ->subject("🚨 Final Hours to Vote: {$projectName}")
            ->greeting("Last chance, {$notifiable->name}!")
            ->line("Voting for the {$projectName} project closes in just {$hoursRemaining} hours! This is your final opportunity to participate in this important community decision.")
            ->line("**Urgent Voting Details:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Topic:** {$votingTopic}")
            ->line("• **Time Remaining:** {$hoursRemaining} hours")
            ->line("• **Current Participation:** {$currentParticipation}% of contributors")
            ->when($leadingOption, fn($mail) => $mail->line("• **Currently Leading:** {$leadingOption}"))
            ->line("**Don't Miss Out:**")
            ->line("This is your final opportunity to influence this important project decision. Your vote could make the difference in the outcome!")
            ->line("**Why Vote Now:**")
            ->line("• Your contribution gives you a voice in project governance")
            ->line("• Community decisions affect your investment returns")
            ->line("• Democratic participation strengthens our community")
            ->line("• Every vote matters in close decisions")
            ->line("**Act Immediately:**")
            ->line("Don't wait - voting closes automatically when the deadline is reached. Cast your vote now to ensure your voice is heard!")
            ->action('Vote Right Now', url('/mygrownet/dashboard'))
            ->line('Time is running out - vote now!')
            ->salutation('Urgently encouraging participation, The MyGrowNet Team');
    }

    /**
     * Voting results notification
     */
    protected function votingResultsMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $votingTopic = $this->data['voting_topic'];
        $winningOption = $this->data['winning_option'];
        $winningPercentage = $this->data['winning_percentage'] ?? 0;
        $totalParticipation = $this->data['total_participation'] ?? 0;
        $userVoted = $this->data['user_voted'] ?? false;
        $userChoice = $this->data['user_choice'] ?? '';
        $implementationDate = $this->data['implementation_date'] ?? '';
        
        return (new MailMessage)
            ->subject("📊 Voting Results: {$projectName}")
            ->greeting("Results are in, {$notifiable->name}!")
            ->line("The community voting for the {$projectName} project has concluded. Here are the results:")
            ->line("**Voting Results:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Topic:** {$votingTopic}")
            ->line("• **Winning Decision:** {$winningOption}")
            ->line("• **Support Level:** {$winningPercentage}% of votes")
            ->line("• **Participation Rate:** {$totalParticipation}% of contributors voted")
            ->when($userVoted, function ($mail) use ($userChoice, $winningOption) {
                $votedForWinner = $userChoice === $winningOption;
                $resultText = $votedForWinner ? 'Your choice won!' : 'Your choice did not win this time.';
                return $mail->line("• **Your Vote:** {$userChoice} - {$resultText}");
            })
            ->when(!$userVoted, fn($mail) => $mail->line("• **Your Participation:** You did not vote in this round"))
            ->when($implementationDate, fn($mail) => $mail->line("• **Implementation Date:** {$implementationDate}"))
            ->line("**Community Democracy in Action:**")
            ->line("This voting process demonstrates the power of community governance. Every contributor had an equal opportunity to influence this important project decision.")
            ->line("**What Happens Next:**")
            ->line("• The winning decision will be implemented as planned")
            ->line("• Project updates will reflect the community's choice")
            ->line("• Future voting opportunities will continue to arise")
            ->line("• Your ongoing participation remains valuable")
            ->when($userVoted, function ($mail) {
                return $mail->line("**Thank You for Participating:**")
                    ->line("Your vote contributed to this democratic process and helped shape the project's direction.");
            })
            ->when(!$userVoted, function ($mail) {
                return $mail->line("**Future Participation:**")
                    ->line("We encourage you to participate in future votes to help guide community decisions.");
            })
            ->action('View Detailed Results', url('/mygrownet/dashboard'))
            ->line('Democracy at work in our community!')
            ->salutation('Respecting our collective decision, The MyGrowNet Team');
    }

    /**
     * Profit distribution calculated notification
     */
    protected function profitDistributionCalculatedMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $distributionPeriod = $this->data['distribution_period'] ?? 'Quarterly';
        $totalProfit = number_format($this->data['total_profit'] ?? 0, 2);
        $userShare = number_format($this->data['user_share'] ?? 0, 2);
        $userPercentage = $this->data['user_percentage'] ?? 0;
        $paymentDate = $this->data['payment_date'] ?? '';
        $projectPerformance = $this->data['project_performance'] ?? '';
        
        return (new MailMessage)
            ->subject("💰 Profit Distribution: {$projectName}")
            ->greeting("Profit sharing time, {$notifiable->name}!")
            ->line("Great news! The {$distributionPeriod} profit distribution has been calculated for the {$projectName} project.")
            ->line("**Profit Distribution Details:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Period:** {$distributionPeriod}")
            ->line("• **Total Profit Generated:** K{$totalProfit}")
            ->line("• **Your Share:** K{$userShare} ({$userPercentage}%)")
            ->when($paymentDate, fn($mail) => $mail->line("• **Payment Date:** {$paymentDate}"))
            ->when($projectPerformance, fn($mail) => $mail->line("• **Performance:** {$projectPerformance}"))
            ->line("**Your Investment is Paying Off:**")
            ->line("This profit distribution represents the return on your community investment. Your K{$userShare} share demonstrates the power of collective investing!")
            ->line("**Community Success:**")
            ->line("• Total community profit: K{$totalProfit}")
            ->line("• Successful project execution")
            ->line("• Fair profit sharing based on contributions")
            ->line("• Proven community investment model")
            ->line("**Payment Processing:**")
            ->line("Your profit share will be processed and paid according to your preferred payment method. You'll receive a confirmation once the payment is completed.")
            ->line("**Reinvestment Opportunity:**")
            ->line("Consider reinvesting your profits in new community projects to compound your wealth-building efforts!")
            ->action('View Distribution Details', url('/mygrownet/dashboard'))
            ->line('Your community investment is generating returns!')
            ->salutation('Sharing in our success, The MyGrowNet Team');
    }

    /**
     * Profit distribution paid notification
     */
    protected function profitDistributionPaidMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $distributionAmount = number_format($this->data['distribution_amount'] ?? 0, 2);
        $paymentMethod = $this->data['payment_method'] ?? 'Mobile Money';
        $transactionId = $this->data['transaction_id'] ?? '';
        $distributionPeriod = $this->data['distribution_period'] ?? 'Quarterly';
        $totalEarningsToDate = number_format($this->data['total_earnings_to_date'] ?? 0, 2);
        
        return (new MailMessage)
            ->subject("✅ Profit Paid: {$projectName}")
            ->greeting("Payment confirmed, {$notifiable->name}!")
            ->line("Your {$distributionPeriod} profit distribution from the {$projectName} project has been successfully paid!")
            ->line("**Payment Confirmation:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Amount Paid:** K{$distributionAmount}")
            ->line("• **Payment Method:** {$paymentMethod}")
            ->when($transactionId, fn($mail) => $mail->line("• **Transaction ID:** {$transactionId}"))
            ->line("• **Period:** {$distributionPeriod}")
            ->line("• **Total Earnings from this Project:** K{$totalEarningsToDate}")
            ->line("**Community Investment Success:**")
            ->line("This payment represents the tangible results of our community investment approach. Your participation in collective wealth-building is generating real returns!")
            ->line("**Investment Performance:**")
            ->line("• Consistent profit distributions")
            ->line("• Transparent payment processing")
            ->line("• Community-driven project success")
            ->line("• Proven wealth-building model")
            ->line("**Keep Building Wealth:**")
            ->line("• Look for new project opportunities")
            ->line("• Consider reinvesting your profits")
            ->line("• Participate in project governance")
            ->line("• Share your success with your network")
            ->line("**Payment Verification:**")
            ->line("The payment should reflect in your account within a few minutes. Contact support if you have any questions about the transaction.")
            ->action('View Payment History', url('/mygrownet/dashboard'))
            ->line('Community investing is working for you!')
            ->salutation('Celebrating your returns, The MyGrowNet Team');
    }

    /**
     * Quarterly community report notification
     */
    protected function quarterlyCommunityReportMail(object $notifiable): MailMessage
    {
        $reportPeriod = $this->data['report_period'] ?? 'Q1 2025';
        $totalProjects = $this->data['total_projects'] ?? 0;
        $completedProjects = $this->data['completed_projects'] ?? 0;
        $totalCommunityProfit = number_format($this->data['total_community_profit'] ?? 0, 2);
        $userTotalEarnings = number_format($this->data['user_total_earnings'] ?? 0, 2);
        $activeContributions = number_format($this->data['active_contributions'] ?? 0, 2);
        $topPerformingProject = $this->data['top_performing_project'] ?? '';
        
        return (new MailMessage)
            ->subject("📈 Community Report: {$reportPeriod}")
            ->greeting("Community update, {$notifiable->name}!")
            ->line("Here's your {$reportPeriod} MyGrowNet Community Investment Report, showcasing our collective achievements and your personal progress.")
            ->line("**Community Performance ({$reportPeriod}):**")
            ->line("• **Total Projects:** {$totalProjects}")
            ->line("• **Completed Projects:** {$completedProjects}")
            ->line("• **Total Community Profit:** K{$totalCommunityProfit}")
            ->when($topPerformingProject, fn($mail) => $mail->line("• **Top Performing Project:** {$topPerformingProject}"))
            ->line("**Your Personal Performance:**")
            ->line("• **Your Total Earnings:** K{$userTotalEarnings}")
            ->line("• **Active Contributions:** K{$activeContributions}")
            ->line("• **Projects Participated:** Multiple community investments")
            ->line("**Community Impact:**")
            ->line("Our community investment approach is creating real wealth for all participants. Together, we've generated K{$totalCommunityProfit} in profits this quarter!")
            ->line("**Key Achievements:**")
            ->line("• Successful project completions")
            ->line("• Consistent profit distributions")
            ->line("• Growing community participation")
            ->line("• Transparent governance processes")
            ->line("**Looking Ahead:**")
            ->line("• New project opportunities in development")
            ->line("• Enhanced community features coming soon")
            ->line("• Continued focus on profitable investments")
            ->line("• Expanding our collective investment power")
            ->line("**Your Continued Success:**")
            ->line("Your participation in community projects is building real wealth. Keep engaging with new opportunities to maximize your returns!")
            ->action('View Full Report', url('/mygrownet/dashboard'))
            ->line('Together, we\'re building lasting wealth!')
            ->salutation('Proud of our community, The MyGrowNet Team');
    }

    /**
     * New project opportunity notification
     */
    protected function newProjectOpportunityMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $projectType = $this->data['project_type'] ?? 'Investment Opportunity';
        $targetAmount = number_format($this->data['target_amount'] ?? 0, 2);
        $expectedReturns = $this->data['expected_returns'] ?? '';
        $projectDuration = $this->data['project_duration'] ?? '';
        $minimumContribution = number_format($this->data['minimum_contribution'] ?? 0, 2);
        $earlyBirdBonus = $this->data['early_bird_bonus'] ?? '';
        $launchDate = $this->data['launch_date'] ?? '';
        
        return (new MailMessage)
            ->subject("🌟 New Investment Opportunity: {$projectName}")
            ->greeting("Exciting opportunity, {$notifiable->name}!")
            ->line("A new community investment opportunity is coming soon! Based on your successful participation in previous projects, we're giving you early access to this exciting opportunity.")
            ->line("**Opportunity Preview:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Type:** {$projectType}")
            ->line("• **Target Amount:** K{$targetAmount}")
            ->line("• **Minimum Contribution:** K{$minimumContribution}")
            ->when($expectedReturns, fn($mail) => $mail->line("• **Expected Returns:** {$expectedReturns}"))
            ->when($projectDuration, fn($mail) => $mail->line("• **Duration:** {$projectDuration}"))
            ->when($launchDate, fn($mail) => $mail->line("• **Launch Date:** {$launchDate}"))
            ->when($earlyBirdBonus, fn($mail) => $mail->line("• **Early Bird Bonus:** {$earlyBirdBonus}"))
            ->line("**Why You're Getting Early Access:**")
            ->line("As a valued community member with a track record of successful project participation, you're among the first to learn about this opportunity.")
            ->line("**Community Investment Advantages:**")
            ->line("• Shared risk through collective participation")
            ->line("• Professional project management")
            ->line("• Transparent progress reporting")
            ->line("• Democratic decision-making process")
            ->line("• Proven profit-sharing model")
            ->line("**Get Ready:**")
            ->line("Start planning your contribution amount and prepare to participate when the project launches. Early participants often secure the best positions!")
            ->action('Learn More', url('/mygrownet/dashboard'))
            ->line('Another opportunity to build wealth together!')
            ->salutation('Bringing you opportunities, The MyGrowNet Team');
    }

    /**
     * Contribution acknowledged notification
     */
    protected function contributionAcknowledgedMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $contributionAmount = number_format($this->data['contribution_amount'] ?? 0, 2);
        $ownershipPercentage = $this->data['ownership_percentage'] ?? 0;
        $totalRaised = number_format($this->data['total_raised'] ?? 0, 2);
        $targetAmount = number_format($this->data['target_amount'] ?? 0, 2);
        $progressPercentage = $this->data['progress_percentage'] ?? 0;
        $contributorRank = $this->data['contributor_rank'] ?? '';
        
        return (new MailMessage)
            ->subject("✅ Contribution Confirmed: {$projectName}")
            ->greeting("Thank you, {$notifiable->name}!")
            ->line("Your contribution to the {$projectName} community project has been successfully processed and confirmed!")
            ->line("**Contribution Details:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Your Contribution:** K{$contributionAmount}")
            ->line("• **Your Ownership:** {$ownershipPercentage}% of project")
            ->line("• **Total Raised:** K{$totalRaised} of K{$targetAmount}")
            ->line("• **Funding Progress:** {$progressPercentage}% complete")
            ->when($contributorRank, fn($mail) => $mail->line("• **Your Rank:** {$contributorRank} contributor"))
            ->line("**Your Investment Position:**")
            ->line("With your K{$contributionAmount} contribution, you now own {$ownershipPercentage}% of this project and will receive {$ownershipPercentage}% of all profits generated.")
            ->line("**Community Impact:**")
            ->line("Your contribution brings us closer to our funding goal and demonstrates the power of community investing. Every contribution matters!")
            ->line("**What Happens Next:**")
            ->line("• You'll receive regular project updates")
            ->line("• Participate in project governance votes")
            ->line("• Track progress through your dashboard")
            ->line("• Receive profit distributions when generated")
            ->line("**Stay Engaged:**")
            ->line("Your investment is just the beginning. Stay active in project discussions and voting to help ensure the project's success!")
            ->action('Track Your Investment', url('/mygrownet/dashboard'))
            ->line('Welcome to this community investment!')
            ->salutation('Grateful for your participation, The MyGrowNet Team');
    }

    /**
     * Project risk alert notification
     */
    protected function projectRiskAlertMail(object $notifiable): MailMessage
    {
        $projectName = $this->data['project_name'];
        $riskType = $this->data['risk_type'] ?? 'Performance Risk';
        $riskDescription = $this->data['risk_description'] ?? '';
        $mitigationPlan = $this->data['mitigation_plan'] ?? '';
        $impactLevel = $this->data['impact_level'] ?? 'Medium';
        $actionRequired = $this->data['action_required'] ?? false;
        $votingScheduled = $this->data['voting_scheduled'] ?? false;
        
        return (new MailMessage)
            ->subject("⚠️ Project Alert: {$projectName}")
            ->greeting("Important update, {$notifiable->name}")
            ->line("We want to keep you informed about a situation that has arisen with the {$projectName} project that requires community attention.")
            ->line("**Risk Alert Details:**")
            ->line("• **Project:** {$projectName}")
            ->line("• **Risk Type:** {$riskType}")
            ->line("• **Impact Level:** {$impactLevel}")
            ->when($riskDescription, function ($mail) use ($riskDescription) {
                return $mail->line("**Risk Description:**")
                    ->line($riskDescription);
            })
            ->when($mitigationPlan, function ($mail) use ($mitigationPlan) {
                return $mail->line("**Mitigation Plan:**")
                    ->line($mitigationPlan);
            })
            ->line("**Transparency and Communication:**")
            ->line("We believe in complete transparency with our community investors. While this situation requires attention, we're committed to working together to address it effectively.")
            ->when($actionRequired, function ($mail) {
                return $mail->line("**Action Required:**")
                    ->line("This situation requires community input and decision-making. Your participation in the resolution process is important.");
            })
            ->when($votingScheduled, function ($mail) {
                return $mail->line("**Community Vote Scheduled:**")
                    ->line("A community vote will be scheduled to decide on the best course of action. Watch for voting notifications.");
            })
            ->line("**Our Commitment:**")
            ->line("• Complete transparency in all communications")
            ->line("• Professional management of the situation")
            ->line("• Community involvement in decision-making")
            ->line("• Protection of community interests")
            ->line("**Stay Informed:**")
            ->line("We'll continue to provide updates as the situation develops. Your investment and trust are important to us.")
            ->action('View Detailed Update', url('/mygrownet/dashboard'))
            ->line('We\'re committed to protecting your investment.')
            ->salutation('Maintaining transparency, The MyGrowNet Team');
    }

    /**
     * Default email template
     */
    protected function defaultMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('MyGrowNet Community Notification')
            ->line('You have received a community notification from MyGrowNet.')
            ->action('View Dashboard', url('/mygrownet/dashboard'));
    }

    /**
     * Get database message based on notification type
     */
    protected function getDatabaseMessage(): string
    {
        return match ($this->data['type']) {
            'project_launched' => $this->getProjectLaunchedMessage(),
            'project_update_posted' => $this->getProjectUpdatePostedMessage(),
            'project_milestone_reached' => $this->getProjectMilestoneReachedMessage(),
            'project_funding_target_reached' => $this->getProjectFundingTargetReachedMessage(),
            'project_completed' => $this->getProjectCompletedMessage(),
            'voting_opened' => $this->getVotingOpenedMessage(),
            'voting_reminder' => $this->getVotingReminderMessage(),
            'voting_closing_soon' => $this->getVotingClosingSoonMessage(),
            'voting_results' => $this->getVotingResultsMessage(),
            'profit_distribution_calculated' => $this->getProfitDistributionCalculatedMessage(),
            'profit_distribution_paid' => $this->getProfitDistributionPaidMessage(),
            'quarterly_community_report' => $this->getQuarterlyCommunityReportMessage(),
            'new_project_opportunity' => $this->getNewProjectOpportunityMessage(),
            'contribution_acknowledged' => $this->getContributionAcknowledgedMessage(),
            'project_risk_alert' => $this->getProjectRiskAlertMessage(),
            default => 'MyGrowNet community notification'
        ];
    }

    /**
     * Get notification priority
     */
    protected function getNotificationPriority(): string
    {
        return match ($this->data['type']) {
            'project_risk_alert', 'voting_closing_soon' => 'high',
            'project_completed', 'profit_distribution_paid', 'voting_opened', 'voting_results', 'project_funding_target_reached' => 'medium',
            'project_launched', 'project_update_posted', 'project_milestone_reached', 'voting_reminder', 'profit_distribution_calculated', 'quarterly_community_report', 'new_project_opportunity', 'contribution_acknowledged' => 'normal',
            default => 'normal'
        ];
    }

    // Database message methods
    protected function getProjectLaunchedMessage(): string
    {
        $projectName = $this->data['project_name'];
        return "🚀 New community project launched: {$projectName}. Join the investment opportunity!";
    }

    protected function getProjectUpdatePostedMessage(): string
    {
        $projectName = $this->data['project_name'];
        $updateTitle = $this->data['update_title'] ?? 'Project Update';
        return "📈 {$projectName} update: {$updateTitle}";
    }

    protected function getProjectMilestoneReachedMessage(): string
    {
        $projectName = $this->data['project_name'];
        $milestoneName = $this->data['milestone_name'];
        return "🎯 {$projectName} milestone reached: {$milestoneName}";
    }

    protected function getProjectFundingTargetReachedMessage(): string
    {
        $projectName = $this->data['project_name'];
        $targetAmount = number_format($this->data['target_amount'] ?? 0, 2);
        return "🎉 {$projectName} fully funded! K{$targetAmount} raised by community.";
    }

    protected function getProjectCompletedMessage(): string
    {
        $projectName = $this->data['project_name'];
        $userShare = number_format($this->data['user_share'] ?? 0, 2);
        return "🏆 {$projectName} completed! Your profit: K{$userShare}";
    }

    protected function getVotingOpenedMessage(): string
    {
        $projectName = $this->data['project_name'];
        $votingTopic = $this->data['voting_topic'];
        return "🗳️ Community vote opened for {$projectName}: {$votingTopic}";
    }

    protected function getVotingReminderMessage(): string
    {
        $projectName = $this->data['project_name'];
        return "⏰ Reminder: Vote needed for {$projectName} community decision.";
    }

    protected function getVotingClosingSoonMessage(): string
    {
        $projectName = $this->data['project_name'];
        $hoursRemaining = $this->data['hours_remaining'] ?? 24;
        return "🚨 Final {$hoursRemaining} hours to vote on {$projectName} decision!";
    }

    protected function getVotingResultsMessage(): string
    {
        $projectName = $this->data['project_name'];
        $winningOption = $this->data['winning_option'];
        return "📊 {$projectName} voting results: {$winningOption} wins!";
    }

    protected function getProfitDistributionCalculatedMessage(): string
    {
        $projectName = $this->data['project_name'];
        $userShare = number_format($this->data['user_share'] ?? 0, 2);
        return "💰 {$projectName} profit distribution: K{$userShare} calculated for you.";
    }

    protected function getProfitDistributionPaidMessage(): string
    {
        $projectName = $this->data['project_name'];
        $distributionAmount = number_format($this->data['distribution_amount'] ?? 0, 2);
        return "✅ {$projectName} profit paid: K{$distributionAmount} transferred to your account.";
    }

    protected function getQuarterlyCommunityReportMessage(): string
    {
        $reportPeriod = $this->data['report_period'] ?? 'Q1 2025';
        $userTotalEarnings = number_format($this->data['user_total_earnings'] ?? 0, 2);
        return "📈 {$reportPeriod} community report: K{$userTotalEarnings} total earnings.";
    }

    protected function getNewProjectOpportunityMessage(): string
    {
        $projectName = $this->data['project_name'];
        return "🌟 New investment opportunity: {$projectName} - Early access available!";
    }

    protected function getContributionAcknowledgedMessage(): string
    {
        $projectName = $this->data['project_name'];
        $contributionAmount = number_format($this->data['contribution_amount'] ?? 0, 2);
        return "✅ {$projectName} contribution confirmed: K{$contributionAmount} invested.";
    }

    protected function getProjectRiskAlertMessage(): string
    {
        $projectName = $this->data['project_name'];
        $riskType = $this->data['risk_type'] ?? 'Performance Risk';
        return "⚠️ {$projectName} alert: {$riskType} - Community attention required.";
    }
}