@extends('layouts.app')

@section('view.stylesheet')
	<link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">
@endsection

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Privacy Policy</div>
				<div class="panel-body">
					<h4>The Directory Privacy Policy was updated on {{ $date }}.</h4>
					<h4>Your privacy is important to Directory. So we’ve developed a Privacy Policy that covers how we collect, use, disclose, transfer, and store your information.</h4>
					<hr><h3>Collection and Use of Personal Information</h3>
					<p>Personal information is data that can be used to identify or contact a single person.</p>
					<p>You may be asked to provide your personal information anytime you are in contact with Directory or a Directory affiliated company. Directory and its affiliates may share this personal information with each other and use it consistent with this Privacy Policy. They may also combine it with other information to provide and improve our products, services, content, and advertising. You are not required to provide the personal information that we have requested, but, if you chose not to do so, in many cases we will not be able to provide you with our products or services or respond to any queries you may have. Here are some examples of the types of personal information Directory may collect and how we may use it:</p>
					<h4>What personal information we collect</h4>
					<ul>
						<li>When you create a Trustee<i class="fa fa-registered"></i> app, participate in an online Directory, contact us or participate in an online survey, we may collect a variety of information, including your name, phone number, email address, contact preferences, and credit card information.</li>
					</ul>
					<h4>What personal information we can access during support</h4>
					<ul>
						<li>In our role as your Trustee support organization, the staff of HIE of One has access to your Trustee and the personal information it contains. We do not collect or store this information beyond the time it takes to resolve a support incident. Our administrative and support staff is trained in this privacy policy. Your information is not shared with third-parties.</li>
						<li>Your Trustee and the personal information it contains is hosted without encryption at an established cloud service provider. This could expose your personal information to support staff at the cloud service provider.</li>
					</ul>
					<h4>How we use your personal information</h4>
					<ul>
						<li>The personal information we collect allows us to keep you posted on Directory’s latest product announcements, software updates, and upcoming events. If you don’t want to be on our mailing list, you can opt out anytime by updating your preferences on your Trustee app.</li>
						<li>We also use personal information to help us create, develop, operate, deliver, and improve our products, services, content and advertising, and for loss prevention and anti-fraud purposes.</li>
						<li>We may use your personal information, including date of birth, to verify identity, assist with identification of users, and to determine appropriate services.</li>
						<li>From time to time, we may use your personal information to send important notices, such as communications about purchases and changes to our terms, conditions, and policies. Because this information is important to your interaction with Directory, you may not opt out of receiving these communications.</li>
						<li>We may also use personal information for internal purposes such as auditing, data analysis, and research to improve Directory’s products, services, and customer communications.</li>
					</ul>
					<h4>Access consent policies for your personal information</h4>
					<ul>
						<li>Directory initializes your Trustee consent policies such that any provider with a valid National Provider Identifier (NPI) can get access to your personal information. You can disable this consent policy, but if you do, you will need to specifically invite each provider that is to have access to your personal information one by one.</li>
					</ul>
					<hr><h3>Collection and Use of Non-Personal Information</h3>
					<p>We also collect data in a form that does not, on its own, permit direct association with any specific individual. We may collect, use, transfer, and disclose non-personal information for any purpose. The following are some examples of non-personal information that we collect and how we may use it:</p>
					<ul>
						<li>We may collect information such as occupation, language, zip code, area code, unique device identifier, referrer URL, location, and the time zone where a Directory-attached Trustee product is used so that we can better understand customer behavior and improve our products, services, and advertising.</li>
						<li>We may collect information regarding customer activities on our website, from the Trustee app, and from our other products and services. This information is aggregated and used to help us provide more useful information to our customers and to understand which parts of our website, products, and services are of most interest. Aggregated data is considered non‑personal information for the purposes of this Privacy Policy.</li>
						<li>We may collect and store details of how you use our services, including search queries. This information may be used to improve the relevancy of results provided by our services. Except in limited instances to ensure quality of our services over the Internet, such information will not be associated with your IP address.</li>
						<li>With your explicit consent, we may collect data about how you use your device and applications in order to help app developers improve their apps.</li>
					</ul>
					<p>If we do combine non-personal information with personal information the combined information will be treated as personal information for as long as it remains combined.</p>
					<hr><h3>Cookies and Other Technologies</h3>
					<p>Directory’s websites, online services, interactive applications, email messages, and advertisements may use "cookies" and other technologies such as pixel tags and web beacons. These technologies help us better understand user behavior, tell us which parts of our websites people have visited, and facilitate and measure the effectiveness of advertisements and web searches. We treat information collected by cookies and other technologies as non‑personal information.  However, to the extent that Internet Protocol (IP) addresses or similar identifiers are considered personal information by local law, we also treat these identifiers as personal information. Similarly, to the extent that non-personal information is combined with personal information, we treat the combined information as personal information for the purposes of this Privacy Policy.</p>
					<p>Directory and our partners also use cookies and other technologies to remember personal information when you use our website, online services, and applications. Our goal in these cases is to make your experience with Directory more convenient and personal. For example, knowing your first name lets us welcome you the next time you visit the Directory. And knowing your contact information, hardware identifiers, and information about your computer or device helps us personalize your operating system, set up your Trustee service, and provide you with better customer service.</p>
					<p>If you want to disable cookies check with your provider to find out how to disable cookies. Please note that certain features of the Directory website will not be available once cookies are disabled.</p>
					<p>As is true of most internet services, we gather some information automatically and store it in log files. This information includes Internet Protocol (IP) addresses, browser type and language, Internet service provider (ISP), referring and exit websites and applications, operating system, date/time stamp, and clickstream data.</p>
					<p>We use this information to understand and analyze trends, to administer the site, to learn about user behavior on the site, to improve our product and services, and to gather demographic information about our user base as a whole. Directory may use this information in our marketing and advertising services.</p>
					<p>In some of our email messages, we use a “click-through URL” linked to content on the Directory website. When customers click one of these URLs, they pass through a separate web server before arriving at the destination page on our website. We track this click-through data to help us determine interest in particular topics and measure the effectiveness of our customer communications. If you prefer not to be tracked in this way, you should not click text or graphic links in the email messages.</p>
					<p>Pixel tags enable us to send email messages in a format customers can read, and they tell us whether mail has been opened. We may use this information to reduce or eliminate messages sent to customers.</p>
					<hr><h3>Disclosure to Third Parties</h3>
					<p>At times Directory may make certain personal information available to strategic partners that work with Directory to provide products and services, or that help Directory market to customers. For example, when you purchase and activate your Trustee, you authorize Directory and your Trustee host service provider to exchange the information you provide during the activation process to carry out service. If you are approved for service, your account will be governed by Directory and your host’s respective privacy policies. Personal information will only be shared by Directory to provide or improve our products, services and advertising; it will not be shared with third parties for their marketing purposes.</p>
					<h4>Service Providers</h4>
					<p>Directory shares personal information with companies who provide services such as information processing, extending credit, fulfilling customer orders, delivering products to you, managing and enhancing customer data, providing customer service, assessing your interest in our products and services, and conducting customer research or satisfaction surveys. These companies are obligated to protect your information and may be located wherever Directory operates.</p>
					<h4>Others</h4>
					<p>It may be necessary − by law, legal process, litigation, and/or requests from public and governmental authorities within or outside your country of residence − for Directory to disclose your personal information. We may also disclose information about you if we determine that for purposes of national security, law enforcement, or other issues of public importance, disclosure is necessary or appropriate.</p>
					<p>We may also disclose information about you if we determine that disclosure is reasonably necessary to enforce our terms and conditions or protect our operations or users. Additionally, in the event of a reorganization, merger, or sale we may transfer any and all personal information we collect to the relevant third party.</p>
					<hr><h3>Protection of Personal Information</h3>
					<p>Directory takes the security of your personal information very seriously. Directory online services protect your personal information during transit using encryption such as Transport Layer Security (TLS). When your personal data is stored by Directory, we use computer systems with limited access housed in Digital Ocean, Inc. facilities using physical security measures.  When you use some Directory products, services, or applications or post on a Directory or HIE of One forum, chat room, or social networking service, the personal information and content you share is visible to other users and can be read, collected, or used by them. You are responsible for the personal information you choose to share or submit in these instances. For example, if you list your name and email address in a forum posting, that information is public. Please take care when using these features.</p>
					<p>If you or anyone else with access to your Trustee logs on to a device that is owned by a third party, any information in your Trustee and accessible to that user including sensitive health information that you have authorized for access by that user—may be downloaded on to that third-party device thereby disclosing any such shared information.</p>
					<h3>Integrity and Retention of Personal Information</h3>
					<p>Directory makes it easy for you to keep your personal information accurate, complete, and up to date. We will retain your personal information for the period necessary to fulfill the purposes outlined in this Privacy Policy unless a longer retention period is required or permitted by law.</p>
					<hr><h3>Access to Personal Information</h3>
					<p>You can help ensure that your contact information and preferences are accurate, complete, and up to date by logging in to your account at <a href="https://dir.hieofone.org/" target="_blank">https://dir.hieofone.org</a>. For other personal information we hold, we will provide you with access (including a copy) for any purpose including to request that we correct the data if it is inaccurate or delete the data if Directory is not required to retain it by law or for legitimate business purposes. We may decline to process requests that are frivolous/vexatious, jeopardize the privacy of others, are extremely impractical, or for which access is not otherwise required by local law. Access, correction, or deletion requests can be made through the Privacy Contact Form.</p>
					<hr><h3>Children & Education</h3>
					<p>We understand the importance of taking extra precautions to protect the privacy and safety of children using Directory products and services. Children under the age of 13, or equivalent minimum age in the relevant jurisdiction, are not permitted to create their own Trustees, unless their parent provided verifiable consent or as part of the child account creation process.</p>
					<p>If we learn that we have collected the personal information of a child under 13, or equivalent minimum age depending on jurisdiction, outside the above circumstances we will take steps to delete the information as soon as possible.</p>
					<p>If at any time a parent needs to access, correct, or delete data associated with their child’s Trustee, they may contact us through our Privacy Contact Form.</p>
					<hr><h3>Third‑Party Sites and Services</h3>
					<p>Directory websites, products, applications, and services may contain links to third-party websites, products, and services. Our products and services may also use or offer products or services from third parties − for example, a Digital Ocean virtual machine.</p>
					<p>Information collected by third parties, which may include such things as location data or contact details, is governed by their privacy practices. We encourage you to learn about the privacy practices of those third parties.</p>
					<hr><h3>Our Companywide Commitment to Your Privacy</h3>
					<p>To make sure your personal information is secure, we communicate our privacy and security guidelines to Directory employees and strictly enforce privacy safeguards within the company.</p>
					<hr><h3>Privacy Questions</h3>
					<p>If you have any questions or concerns about Directory’s Privacy Policy or data processing or if you would like to make a complaint about a possible breach of local privacy laws, please contact us. You can always contact us by email at <a href="mailto:info@healthurl.com">info@healthurl.com</a>.</p>
					<p>When a privacy question or access/download request is received we have a dedicated team which triages the contacts and seeks to address the specific concern or query which you are seeking to raise. Where your issue may be more substantive in nature, more information may be sought from you. All such substantive contacts receive a response. If you are unsatisfied with the reply received, you may refer your complaint to the relevant regulator in your jurisdiction. If you ask us, we will endeavor to provide you with information about relevant complaint avenues which may be applicable to your circumstances.</p>
					<p>Directory may update its Privacy Policy from time to time. When we change the policy in a material way, a notice will be posted on our website along with the updated Privacy Policy.</p>
					<p>HIE of One, PBC. 52 Marshall St., Watertown, MA, 02472</p>
			</div>
		</div>
	</div>
</div>
@endsection

@section('view.scripts')
<script src="{{ asset('assets/js/toastr.min.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function() {
		toastr.options = {
            'closeButton': true,
            'debug': false,
            'newestOnTop': true,
            'progressBar': true,
            'positionClass': 'toast-bottom-full-width',
            'preventDuplicates': false,
            'showDuration': '300',
            'hideDuration': '1000',
            'timeOut': '5000',
            'extendedTimeOut': '1000',
            'showEasing': 'swing',
            'hideEasing': 'linear',
            'showMethod': 'fadeIn',
            'hideMethod': 'fadeOut'
        };
	});
</script>
@endsection
