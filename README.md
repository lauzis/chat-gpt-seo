# Chat GPT seo
Plugin for analysing content, and using chat gpt to generate seo meta description from content

# What it does
- Scans content pages (pages/posts) and validates against soe best practices
- Also checks if the content has keywords in important sections of the content
- Possible to update meta description / generate it trough chatGpt, based on content of particular page
- Gives "penalty" score for each content item

# Prerequisites
- ACF PRO plugin
- Yoast Seo plugin

# Todos and ides
- Get data from search tool, what are keywords that gets visits
- Crawl versioning / compare
- Add possibility to list what post types should be analysed
- Add possibility to add some pages to ignore list
- Move the default chat gpt context out to the settings page

# Change log
--- version 1.0.12 ---
- some refactoring
- added buttons for clearing all the cashed audit data
- instead of auto starting audit added button for starting running the audit
- added keyword audit page / keyword use stats page
- added authentication to the api requests
- local keyword flag for in the table

--- version 1.0.10 ---
- bugfixes and code cleanup
- added table filtering / search
- added fixed administration menu
- keywords coming with a fallback if there is not defined for page then use default keywords

--- initial MVP---
