# Solve With Google Hackathon
## Maxima's Data Stars

Creating moments that last.

We envision a future where no parent or sibling experiences regret, knowing they have made the most of every moment with their loved one.

### Our solution:

We propose a generated activities to do specifically designed for you, knowing the capabilities of your child/sibling and the resources available. With the ultimate goal of creating precious memories together. 

Target audience:
Parents and siblings of the patient

Main screen when you open the app:<br>
<img width="700" alt="main_screen" src="https://github.com/DTH-sensire/maximadatastars/blob/main/images/main_screen.png">

An example question:<br>
<img width="700" alt="main_screen" src="https://github.com/DTH-sensire/maximadatastars/blob/main/images/example_question.png">

### General architecture

There will be an dynamic webapp where parents or siblings get questions about their child/sibling. Medical and/or general data like age and diagnosis about the child will be available in BigQuery.
In a production environment this data will be collected from the EHR.
The answers collected through the webapp combined with the data from BigQuery will be used as the input for an API call to a pyhton script on Cloud Run. The pyhton script will generate a prompt for Vertex AI.
Vertex AI (Gemini) will be send the result, what is a great activity to do, back to the webapp.

<img width="700" alt="main_screen" src="https://github.com/DTH-sensire/maximadatastars/blob/main/images/architecture.png">

### Improvements for production

* Answers given in the webapp should be stored in BigQuery.
* Not calling  the python script directly but based on the insert of data from the webapp in BigQuery.
* Make the webapp conversational. The user should have to comment on the activitity. There should also be a possibility to say that you have done the activity, let the app know how you experienced the activity and ask for another.
* Dynamic questions based on the age of parent or sibling. A sibling can get the same question but easier to understand.


