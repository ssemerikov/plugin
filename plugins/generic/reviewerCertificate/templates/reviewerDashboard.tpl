{**
 * plugins/generic/reviewerCertificate/templates/reviewerDashboard.tpl
 *
 * Copyright (c) 2024
 * Distributed under the GNU GPL v3.
 *
 * Certificate download button for reviewer dashboard
 *}

{if $showCertificateButton && $certificateUrl}
	<div class="reviewer-certificate-section">
		<h3>{translate key="plugins.generic.reviewerCertificate.certificateAvailable"}</h3>
		<p>{translate key="plugins.generic.reviewerCertificate.certificateAvailableDescription"}</p>
		<a href="{$certificateUrl}" class="pkp_button certificate-download-button" target="_blank">
			<span class="fa fa-certificate"></span>
			{translate key="plugins.generic.reviewerCertificate.downloadCertificate"}
		</a>
	</div>
{/if}
