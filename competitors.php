<?php

// Things to notice:
// You need to add your Analysis and Design element of the coursework to this script
// There are lots of web-based survey tools out there already.
// It’s a great idea to create trial accounts so that you can research these systems. 
// This will help you to shape your own designs and functionality. 
// Your analysis of competitor sites should follow an approach that you can decide for yourself. 
// Examining each site and evaluating it against a common set of criteria will make it easier for you to draw comparisons between them. 
// You should use client-side code (i.e., HTML5/JavaScript/jQuery) to help you organise and present your information and analysis 
// For example, using tables, bullet point lists, images, hyperlinking to relevant materials, etc.

// execute the header script:
require_once "header.php";

if (!isset($_SESSION['loggedIn']))
{
	// user isn't logged in, display a message saying they must be:
	echo "You must be logged in to view this page.<br>";
}
else
{
	$star_1 = "<img title='_STAR_NUM_' src='img/star.png'/>";
	$star_2 = "<img title='_STAR_NUM_' src='img/star.png'/>".$star_1;
	$star_3 = "<img title='_STAR_NUM_' src='img/star.png'/>".$star_2;
	$star_4 = "<img title='_STAR_NUM_' src='img/star.png'/>".$star_3;
	$star_5 = "<img title='_STAR_NUM_' src='img/star.png'/>".$star_4;
	$star_1 = str_replace("_STAR_NUM_", '1 star', $star_1);
	$star_2 = str_replace("_STAR_NUM_", '2 star', $star_2);
	$star_3 = str_replace("_STAR_NUM_", '3 star', $star_3);
	$star_4 = str_replace("_STAR_NUM_", '4 star', $star_4);
	$star_5 = str_replace("_STAR_NUM_", '5 star', $star_5);
	echo <<<_END
<div class="report">
<h2>Competitor Analysis</h2>
<p>In order to gain a more complete understanding of the implementation of a survey website, I looked at three examples of prominent competitors in the area.</p>
<ul>
	<li>
		<h3>Google Forms</h3>
		<section>
			<img title="click to enlarge"class="img_right expandable"default-width="350" width="1001" height="823" src="img/googleforms1.png"/>
			<p>
			<a href='https://www.google.com/forms/about/'>Google Forms</a> is Google's survey system. it provides a clean way to create forms for a wide variety of uses.
			</p>
			<p>
			The Google Forms site has the ability to create forms with multiple questions. You can edit the title and description of each question, along with the answer type.
			</p>
			<p>
			The image to the right displays all of the data types Google Forms allows.
			</p>
		</section>
		
		<section>
			<h4 class='centered'>Rating</h4>
			<table class='competitor'>
				<tr><th>Layout</th><td class='stars'>$star_3</td><td>A little too spaced out, which makes it harder to find what you need quickly</td></tr>
				<tr><th>Ease of Use</th><td class='stars'>$star_4</td><td>Standard Google ease of use, simple and clear instructions</td></tr>
				<tr><th>User Account</th><td class='stars'>$star_5</td><td>Everybody has a Google account</td></tr>
				<tr><th>Question Types</th><td class='stars'>$star_3</td><td>You have to write all the questions and answers yourself</td></tr>
				<tr><th>Analysis</th><td class='stars'>$star_4</td><td>All your responses can be shown by clicking "responses" - and full access to Google's analysis options</td></tr>
			</table>
			</p>
		</section>
	</li>	
	<hr>
	<li>
	<h3>Survey Monkey</h3>
		<section>
		<img title="click to enlarge"class="img_left expandable"default-width="350" width="960" height="540" src="img/surveymonkey1.png"/>
			<p>
			<a href='https://www.surveymonkey.com/'>Survey Monkey</a> is another popular survey solution. It has been around for a long time, since 1999, and has powerful backers; and reportedly gets over 20 million answers every day! 
			</p>
			<p>
			Survey Monkey has several powerful features that other competitiors don't have, including a "suggested questions" feature which dynamically suggests recommended questions as you type in your question, based on their massive database of questions.
			</p>
			<p>
			The image above displays a typical survey creation screen.
			</p>
		</section>
		<section>
			<h4 class='centered'>Rating</h4>
			<table class='competitor'>
				<tr><th>Layout</th><td class='stars'>$star_2</td><td>Too much information in most cases, it can be overwhelming</td></tr>
				<tr><th>Ease of Use</th><td class='stars'>$star_3</td><td>Hard to decipher each option and question setting</td></tr>
				<tr><th>User Account</th><td class='stars'>$star_3</td><td>Simple free sign up requiring name and email address, but many options restricted until you pay</td></tr>
				<tr><th>Question Types</th><td class='stars'>$star_5</td><td>Suggested questions, and a wide variety of question types</td></tr>
				<tr><th>Analysis</th><td class='stars'>$star_5</td><td>In-depth analysis available for free, but you have to upgrade for .csv/.xls downloading</td></tr>
			</table>
			</p>
		</section>
	</li>
	<hr>
	<li>
	<h3>Survey Planet</h3>
		<section>
		<img title="click to enlarge"class="img_right expandable"default-width="350" width="619" height="453" src="img/surveyplanet.png"/>
			<p>
			<a href='https://app.surveyplanet.com/login'>Survey Planet</a> is a more recent competitor, which adopts a rather "bare-bones" approach to survey creation.
			</p>
			<p>
			With the Survey Planet system you can create any number of surveys and receive any number of responses for free, which is a massive advantage over the more high definition solutions.
			</p>
			<p>
			The image above shows the Survey Planet survey maker.
			</p>
		</section>
		<section>
			<h4 class='centered'>Rating</h4>
			<table class='competitor'>
				<tr><th>Layout</th><td class='stars'>$star_3</td><td>A clean interface for the user, but with limited control on position and formatting</td></tr>
				<tr><th>Ease of Use</th><td class='stars'>$star_5</td><td>Super-simple interface makes creating questions easy</td></tr>
				<tr><th>User Account</th><td class='stars'>$star_3</td><td>Many features enabled for free, but the sign up process is a little unfriendly</td></tr>
				<tr><th>Question Types</th><td class='stars'>$star_2</td><td>Many basic question types, but a distinct lack of more complex data-based questions</td></tr>
				<tr><th>Analysis</th><td class='stars'>$star_2</td><td>Free version only contains limited reports, and paid version adds little extra</td></tr>
			</table>
			</p>
		</section>
	</li>		
</ul>
</div>

_END;
}

// finish off the HTML for this page:
require_once "footer.php";
?>