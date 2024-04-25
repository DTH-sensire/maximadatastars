"""Prompt a model using most relevant samples in a BigQuery table."""
from langchain.prompts.prompt import PromptTemplate
from langchain_google_vertexai import VertexAI
from langchain.schema import StrOutputParser
from operator import itemgetter
from langchain_core.runnables import RunnablePassthrough

from flask import Flask, request, jsonify

from google.cloud import bigquery


import os

PROJECT_ID = os.environ.get("_PROJECT_ID")

NO_DATA_FOUND_ANSWER = (
    "Geen dagrapportes gevonden voor de klant op de gegeven data."
)


ini_temp = """
Als een specialist in de verpleegkunde, maak een concrete en klinisch relevante samenvatting van de aangeleverde tekst, rekening houdend met de volgende richtlijnen:
1. Maak een korte gedetailleerde samenvatting, zonder verlies van belangrijke en klinisch relevantie informatie.
2. Beschrijf het beloop in de tijd en eindig met een zin over de huidige situatie. Houd rekening met de aangeleverde datums, de huidige situatie is de meest recente datum.
3. Ga alleen uit van de aangeleverde tekst, includeer geen extra informatie. Zorg dat er geen informatie in de samenvatting staat dat niet in de aangeleverde tekst staat.
4. Schrijf kort of er veranderingen zijn geweest in één van de ondersteuningsbehoeften: {ondersteuningsbehoeften}. Indien hier niks over staat, zet deze ondersteuningsbehoeften dan niet in de samenvatting.
5. De maximale lengte van de samenvatting is 100 woorden. Het doel is om snel een overzicht te krijgen wat er is gebeurt in de afgelopen periode.

Door deze prompt te volgen, genereer jij een effectieve en klinisch relevante samenvatting dat de essentie van de geleverde tekst beschrijft op een duidelijke, korte en leesbare manier.

De tekst: {dagrapportages}

SAMENVATTING:
"""

feedback_temp = """
Je bent een specialist in de verpleegkunde. Je hebt net een samenvatting geschreven. Vergelijk de samenvatting met de originele tekst. Geef kort feedback en geef aan of er belangrijke informatie mist in de samenvatting en of er informatie in de samenvatting staat wat niet in de originele tekst staat.
Maak drie koppen: "Feedback", "Belangrijke informatie die mist in de samenvatting", "Informatie in de samenvatting die niet in de originele tekst staat".

Originele tekst: {dagrapportages}

Samenvatting: {base_output}

FEEDBACK:
"""

opti_temp = """
Je bent een specialist in de verpleegkunde. Verwerk de feedback in de originele samenvatting. Houd rekening met de max 200 woorden van de totale samenvatting. Verwijder de informatie dat niet in de originele tekst staan in de nieuwe samenvatting.

Originele amenvatting: {base_output}

Feedback: {feedback}

VERBETERDE SAMENVATTING:
"""

app = Flask(__name__)


def get_bigquery_client() -> bigquery.Client:
    return bigquery.Client(project="qwiklabs-gcp-03-c86c31d83de6")


def get_query_patientdata(subject_id, date_range) -> str:
    return (
        'SELECT CONCAT(createdat, " ", content_text) AS datum_dagrapportages FROM `sensire-dataplatform-prod.raw_ysis.rapportage_ecd`'
        f'WHERE clientid = \'{subject_id}\' AND PARSE_TIMESTAMP("%m/%d/%Y %H:%M", createdat) > TIMESTAMP(DATE \'{date_range["start"]}\', "Europe/Brussels") AND '
        f'PARSE_TIMESTAMP("%m/%d/%Y %H:%M", createdat) < TIMESTAMP(DATE \'{date_range["end"]}\', "Europe/Brussels")'
        'ORDER BY datum_dagrapportages ASC'
    )


# COMMAND TO USE API
# curl -X POST https://summarizer-ieex5ttcva-ew.a.run.app \
#    -H 'Content-Type: application/json' \
#    -H "Authorization: Bearer $(gcloud auth print-identity-token)" \
#    -d '{"subject_id":"0", "date_range": {"start": "2020-08-10", "end" : "2020-08-12"}}'

@app.route("/")
def GenerateSummary():
    """ Samenvatten van dagrapportages aan de hand van een subject_id, nu nog in de gehele periode. Maar kan met een periode selectie."""
    print("Welcome to Sensire >_")
    return jsonify({"response": "Welcome to Sensire >_"})
    # subject_id = request.json["subject_id"]
    # date_range = request.json["date_range"]

    # get_dagrapportes = False
    # if "get_dagrapportes" in request.json.keys():
    #     get_dagrapportes = request.json["get_dagrapportes"] == "true"

    # # BQ client opzetten
    # client = get_bigquery_client()

    # # Dagrapportages ophalen
    # QUERY_dr = get_query_dagrapportages(subject_id, date_range)
    # job_dr = client.query(QUERY_dr)
    # rows_dr = job_dr.result()

    # docs_dr = []
    # for row in rows_dr:
    #     for doc in row.values():
    #         docs_dr.append(doc)
    # docs_dr = "\n".join(docs_dr)

    # # Ondersteuningsbehoeften ophalen
    # QUERY_ob = get_query_ondersteuningsbehoeften(subject_id)
    # job_ob = client.query(QUERY_ob)
    # rows_ob = job_ob.result()

    # docs_ob = []
    # for row in rows_ob:
    #     for doc in row.values():
    #         docs_ob.append(doc)
    # docs_ob = "\n".join(docs_ob)

    # if len(docs_dr) == 0:
    #     if get_dagrapportes:
    #         return {"summarization": NO_DATA_FOUND_ANSWER, "dagrapporten": []}
    #     return NO_DATA_FOUND_ANSWER

    # llm = VertexAI(
    #     model_name="gemini-1.5-pro-preview-0409",
    #     max_output_tokens=750,
    #     temperature=0,
    #     top_p=0.8,
    #     top_k=20,
    #     verbose=True,
    # )

    # model_parser = llm | StrOutputParser()

    # eerste_samenvatting = ({"ondersteuningsbehoeften": itemgetter("ondersteuningsbehoeften"), "dagrapportages": itemgetter(
    #     "dagrapportages")} | PromptTemplate.from_template(ini_temp) | model_parser)
    # feedback_samenvatting = ({"dagrapportages": itemgetter("dagrapportages"), "base_output": itemgetter(
    #     "base_output")} | PromptTemplate.from_template(feedback_temp) | model_parser)
    # opti_samenvatting = ({"feedback": itemgetter("feedback"), "base_output": itemgetter(
    #     "base_output")} | PromptTemplate.from_template(opti_temp) | model_parser)

    # chain = ({'base_output': eerste_samenvatting, "dagrapportages": itemgetter("dagrapportages")}
    #          | RunnablePassthrough.assign(feedback=feedback_samenvatting) | opti_samenvatting)

    # final_output = chain.invoke(
    #     {"ondersteuningsbehoeften": docs_ob, "dagrapportages": docs_dr})
    # return (final_output)


PROMPT_TEMPLATE = """
I am the {relation} of {firstname}. I am {relation_age} years old. {firstname} is diagnosed with {diagnosis} and is in stage {diagnosis_stage}.
{firstname} receives the treatment {treatment} and {medicine}.
The symptoms are {symptoms}. {firstname} is a {gender} born on {date_of_birth}.

I answered some questions to specify the activities that {firstname} would like:

What are their interests?: {interests}
Do they have any favorite books, movies, or TV shows?: {favorites}
Do they enjoy socialising or do they prefer quiet solo activities?: {social_interaction}
Do they like creative activities such as drawing, arts and crafts, and music?: {creative}
Are they curious and eager to learn? Are there subjects they are particularly interested in?: {curious}
Do they enjoy outdoor activities and nature experiences?: {nature}
Do they have an animal affinity?: {animals}
Do digital formats like computers, games, and videos pique their interest?: {technology}
Have they expressed a wish to try something specific?: {wishes}
Would they prefer indoor activities or spending time in the open air?: {outside}


I would like do activities with {firstname}, can you make a list of recommendations based on his capabilities and the place we live: {residence}?

RECOMMENDATIONS:
"""


@app.route("/test", methods=["POST"])
def test():
    response = request.json["response"]
    # Retrieve PatientData dictionary
    patient_data = response["patientData"]

    # Retrieve AnswersQuestions list
    answers_questions = response["answersAndQuestions"]
    answers = {}  # make dictionary with question as key and answer as value
    for item in answers_questions:
        answers[item['question']] = item['answer']

    # Replace the placeholders in the PROMPT_TEMPLATE
    PROMPT_TEMPLATE.format(relation=answers[f"What is your relationship with {patient_data["firstname"]}"],
                           firstname=patient_data["firstname"],
                           relation_age=answers["What is your age?"],
                           diagnosis=patient_data["diagnosis"],
                           diagnosis_stage=patient_data["diagnosis_stage"],
                           treatment=patient_data["treatment"],
                           medicine=patient_data["medicine"],
                           symptoms=patient_data["symptoms"],
                           gender=patient_data["gender"],
                           date_of_birth=patient_data["date_of_birth"],
                           interests=answers["What are Jenny's interests?"],
                           favorites=answers["Does Jenny have any favorite books, movies, or TV shows?"],
                           social_interaction=answers["Does Jenny enjoy socialising or prefer quiet solo activities?"],
                           creative=answers["Does Jenny like creative activities such as drawing, arts and crafts, and music?"],
                           curious=answers["Is Jenny curious and eager to learn? Are there subjects he\/she is particularly interested in?"],
                           nature=answers["Does Jenny enjoy outdoor activities and nature experiences?"],
                           animals=answers["Does Jenny have an animal affinity?"],
                           technology=answers["Do digital formats like computers, games, and videos pique Jenny's interest?"],
                           wishes=answers["Have Jenny expressed a wish to try something specific?"],
                           outside=answers["Would Jenny prefer indoor activities or spending time in the open air?"],
                           residence=patient_data["residence"],
                           )

    response = {"response": PROMPT_TEMPLATE}
    return response


if __name__ == "__main__":
    # LOGGER.debug("Launching server...")
    # app.run(debug=True, host="0.0.0.0", port=int(os.environ.get("PORT", 8080)))
    app.run(debug=True, port=8080, host="0.0.0.0")

# europe-west4-docker.pkg.dev/qwiklabs-gcp-03-c86c31d83de6/sensire-docker
# gcloud builds submit --region=europe-west4 --tag europe-west4-docker.pkg.dev/qwiklabs-gcp-03-c86c31d83de6/sensire-docker:latest .
