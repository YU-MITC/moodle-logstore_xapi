# moodle-logstore_xapi
Customized Moodle xAPI logstore plugin

This plugin emits xAPI (Experience API) log statements to an LRS (Learning Record Store).
The xAPI is a standard for learning logs.

And, the "Learning Locker" is well known as xAPI-compliant LRS.

In order to emit detailed log statements (e.g. answers value for quiz, grades, and responses for questionnaire), conversion scripts for each event and question type are required.
xAPI formatted logs stored in the LRS are very useful for "Learning Analytics".
However, traditional xAPI plugin only supports a small number of plugins and question types.

So, we have added/modified PHP scripts in order to more plugin events and question types.

We have added the following plugins to supported plugins.

- mod_kalmediaassign (2 events)
- mod_kalmediares (2 events)
- mod_questionnaire (2 events, 7 question type)
- mod_vpl (7 events)
- mod_workshop (6 events)

Additionally, we have added the following question types to supported question types.

- Calculated
- Calculated multichoice
- Calculated simple
- Drag and drop into text
- Drag and drop markers
- Drag and drop onto image
- Pattern match with molecular editor
- Stack

Now, codes of this plugin are based on the xAPI plugin (logstore_xapi) version 4.6.0.

